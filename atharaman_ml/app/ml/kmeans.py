import os
import joblib
import numpy as np
import pandas as pd
from sklearn.cluster import KMeans
from sklearn.compose import ColumnTransformer
from sklearn.metrics import silhouette_score
from sklearn.pipeline import Pipeline
from sklearn.preprocessing import StandardScaler, OneHotEncoder

MODEL_DIR = os.path.join(os.path.dirname(__file__), "..", "..", "artifacts")
MODEL_PATH = os.path.join(MODEL_DIR, "tourist_kmeans_model.joblib")
METRICS_PATH = os.path.join(MODEL_DIR, "kmeans_metrics.joblib")

# Aligned strictly with location persona discovery
NUMERIC_FEATURES = ["age"]
CATEGORICAL_FEATURES = ["preferred_travel_style", "country"]

def build_preprocessor() -> ColumnTransformer:
    return ColumnTransformer(transformers=[
        ("num", StandardScaler(), NUMERIC_FEATURES),
        ("cat", OneHotEncoder(handle_unknown="ignore"), CATEGORICAL_FEATURES),
    ])

def build_clustering_pipeline(k: int) -> Pipeline:
    return Pipeline(steps=[
        ("preprocessor", build_preprocessor()),
        ("clusterer", KMeans(n_clusters=k, random_state=42, n_init=20)),
    ])

def train_and_save_model(
    tourists_df: pd.DataFrame, 
    interactions_df: pd.DataFrame, 
    services: dict, 
    min_k: int = 2, 
    max_k: int = 8
):
    print("Training K-Means Tourist Persona Model...")
    
    train_data = tourists_df.copy()
    train_data["country"] = train_data["country"].fillna("Unknown")
    train_data = train_data.dropna(subset=["preferred_travel_style", "age"])

    if len(train_data) < 4:
        print("Not enough tourist records to cluster.")
        return None, None, None

    transformed = build_preprocessor().fit_transform(train_data)
    if hasattr(transformed, "toarray"):
        transformed = transformed.toarray()

    upper_k = min(max_k, len(train_data) - 1)
    best_k = min_k
    best_score = -1.0
    metrics = {}

    for k in range(min_k, upper_k + 1):
        model = KMeans(n_clusters=k, random_state=42, n_init=20)
        labels = model.fit_predict(transformed)
        if len(np.unique(labels)) < 2: continue
        
        score = silhouette_score(transformed, labels)
        metrics[k] = {"silhouette_score": float(score), "inertia": float(model.inertia_)}
        
        if score > best_score:
            best_score = score
            best_k = k

    print(f"--> Selected K={best_k} using Silhouette Score.")
    pipeline = build_clustering_pipeline(best_k)
    
    # 1. Fit the pipeline and assign clusters to the dataframe
    train_data["cluster"] = pipeline.fit_predict(train_data)

    # 2. THE BRIDGE: Map Clusters to actual Location Preferences
    cluster_prefs = {}
    loc_interactions = interactions_df[interactions_df["item_type"] == "locations"]
    locations_df = services.get("locations", pd.DataFrame())

    if not loc_interactions.empty and not locations_df.empty:
        # Match interactions to location types
        merged_locs = loc_interactions.merge(locations_df[["id", "location_type"]], left_on="item_id", right_on="id")
        # Match users to their new clusters
        full_history = merged_locs.merge(train_data[["user_id", "cluster"]], on="user_id")
        
        for c in range(best_k):
            cluster_data = full_history[full_history["cluster"] == c]
            if not cluster_data.empty:
                # Find the top 2 location types this cluster historically visits
                top_types = cluster_data["location_type"].value_counts().head(2).index.tolist()
                cluster_prefs[c] = top_types

    os.makedirs(MODEL_DIR, exist_ok=True)
    joblib.dump(pipeline, MODEL_PATH)
    
    # Save the preferences securely inside the metrics file!
    metrics["cluster_preferences"] = cluster_prefs
    joblib.dump({"best_k": best_k, "metrics": metrics}, METRICS_PATH)
    
    print(f"✅ K-Means model saved to {MODEL_PATH}")
    return pipeline, best_k, metrics

def predict_cluster(tourist_profile: dict) -> tuple[int, list]:
    if not os.path.exists(MODEL_PATH):
        return 0, []
        
    model = joblib.load(MODEL_PATH)
    metrics_data = joblib.load(METRICS_PATH)
    
    profile_df = pd.DataFrame([tourist_profile])
    profile_df["country"] = profile_df["country"].fillna("Unknown")
    
    prediction = int(model.predict(profile_df)[0])
    
    # Fetch the location types this specific persona loves mathematically
    cluster_prefs = metrics_data.get("metrics", {}).get("cluster_preferences", {})
    preferred_locations = cluster_prefs.get(prediction, [])
    
    return prediction, preferred_locations