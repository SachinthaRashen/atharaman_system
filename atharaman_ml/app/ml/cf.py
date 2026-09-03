import os
import joblib
import numpy as np
import pandas as pd
from sklearn.decomposition import TruncatedSVD

MODEL_DIR = os.path.join(os.path.dirname(__file__), "..", "..", "artifacts")
MODEL_PATH = os.path.join(MODEL_DIR, "cf_svd_model.joblib")
MAPPINGS_PATH = os.path.join(MODEL_DIR, "cf_mappings.joblib")

def normalize_item_type(item_type: str) -> str:
    mapping = {
        "guide": "guides", "guides": "guides",
        "hotel": "hotels", "hotels": "hotels",
        "location": "locations", "locations": "locations",
        "shop_item": "shop_items", "shop_items": "shop_items",
        "vehicle": "vehicles", "vehicles": "vehicles",
        "shop": "shops", "shops": "shops",
    }
    value = str(item_type).strip().lower()
    return mapping.get(value, value)

def make_unique_item_id(item_type: str, item_id) -> str:
    return f"{normalize_item_type(item_type)}_{int(item_id)}"

def build_user_item_matrix(interactions_df: pd.DataFrame, item_columns=None) -> pd.DataFrame:
    df = interactions_df.copy()
    df["unique_item_id"] = df.apply(
        lambda row: make_unique_item_id(row["item_type"], row["item_id"]), axis=1
    )
    
    # Use mean() to average ratings and interactions equally
    matrix = df.pivot_table(
        index="user_id",
        columns="unique_item_id",
        values="preference_score",
        aggfunc="mean" 
    ).fillna(0.0)

    if item_columns is not None:
        matrix = matrix.reindex(columns=item_columns, fill_value=0.0)
    return matrix

def train_svd_model(interactions_df: pd.DataFrame):
    print("Training Collaborative Filtering (SVD) Model...")
    if interactions_df.empty:
        return None, None

    matrix = build_user_item_matrix(interactions_df)
    n_components = min(20, matrix.shape[0] - 1, matrix.shape[1] - 1)
    
    if n_components < 1:
        return None, None

    svd = TruncatedSVD(n_components=n_components, random_state=42)
    svd.fit(matrix)

    os.makedirs(MODEL_DIR, exist_ok=True)
    joblib.dump(svd, MODEL_PATH)
    joblib.dump({
        "users": list(matrix.index),
        "items": list(matrix.columns),
        "n_components": n_components
    }, MAPPINGS_PATH)

    print(f"✅ SVD saved: {MODEL_PATH}")
    return svd, matrix

def load_svd_artifacts():
    if not os.path.exists(MODEL_PATH) or not os.path.exists(MAPPINGS_PATH):
        raise FileNotFoundError("SVD artifacts not found.")
    return joblib.load(MODEL_PATH), joblib.load(MAPPINGS_PATH)

def build_user_vector(user_id: int, interactions_df: pd.DataFrame, item_columns) -> np.ndarray:
    vector = np.zeros(len(item_columns), dtype=float)
    user_history = interactions_df[interactions_df["user_id"] == user_id].copy()

    if user_history.empty:
        return vector

    item_index = {item: idx for idx, item in enumerate(item_columns)}
    
    user_history["unique_id"] = user_history.apply(
        lambda row: make_unique_item_id(row["item_type"], row["item_id"]), axis=1
    )
    
    # Group by item and take the mean
    mean_scores = user_history.groupby("unique_id")["preference_score"].mean()

    for unique_id, score in mean_scores.items():
        if unique_id in item_index:
            idx = item_index[unique_id]
            vector[idx] = float(score)

    return vector

def predict_user_item_scores(user_id: int, interactions_df: pd.DataFrame) -> dict[str, float]:
    try:
        svd, mappings = load_svd_artifacts()
    except FileNotFoundError:
        return {}

    item_columns = mappings["items"]
    user_vector = build_user_vector(user_id, interactions_df, item_columns)

    if not np.any(user_vector):
        return {}

    user_vector_df = pd.DataFrame([user_vector], columns=item_columns)
    latent_user = svd.transform(user_vector_df)
    reconstructed = (latent_user @ svd.components_)[0]

    result = {}
    for item, score in zip(item_columns, reconstructed):
        result[item] = float(np.clip(score, 0.0, 5.0))
    return result