import pandas as pd
import os
# pyrefly: ignore [missing-import]
import joblib
from sklearn.decomposition import TruncatedSVD

# Paths for saving the model and the matrix index mappings
MODEL_PATH = os.path.join(os.path.dirname(__file__), '..', '..', 'artifacts', 'cf_svd_model.joblib')
MAPPINGS_PATH = os.path.join(os.path.dirname(__file__), '..', '..', 'artifacts', 'cf_mappings.joblib')

def train_svd_model(interactions_df: pd.DataFrame):
    """Trains the Matrix Factorization model using SVD."""
    print("Training Collaborative Filtering (SVD) Model...")
    
    # 1. Create a unique ID for items since a hotel and a vehicle might both have ID=1
    interactions_df['unique_item_id'] = interactions_df['item_type'] + '_' + interactions_df['item_id'].astype(str)
    
    # 2. Pivot the data into a User-Item Matrix
    # We use 'mean' in case a user interacted with the same item multiple times
    user_item_matrix = interactions_df.pivot_table(
        index='user_id', 
        columns='unique_item_id', 
        values='implicit_score', 
        aggfunc='mean'
    ).fillna(0)
    
    # 3. Configure the SVD Algorithm
    # We extract latent features. If your dataset is small, we cap the components to avoid errors.
    n_components = min(20, user_item_matrix.shape[1] - 1, user_item_matrix.shape[0] - 1)
    
    # Check if we have enough data to actually factorize
    if n_components < 1:
        print("⚠️ Not enough interaction data to train SVD. Skipping.")
        return None, None

    svd = TruncatedSVD(n_components=n_components, random_state=42)
    
    # 4. Train the model
    svd.fit(user_item_matrix)
    
    # 5. Save the compiled model and the row/column mappings for the FastAPI inference step
    os.makedirs(os.path.dirname(MODEL_PATH), exist_ok=True)
    joblib.dump(svd, MODEL_PATH)
    
    # We save the index/columns so the API knows which user/item corresponds to which row/column in the math
    joblib.dump({'users': user_item_matrix.index, 'items': user_item_matrix.columns}, MAPPINGS_PATH)
    
    print(f"✅ SVD Model saved successfully to {MODEL_PATH}")
    
    return svd, user_item_matrix