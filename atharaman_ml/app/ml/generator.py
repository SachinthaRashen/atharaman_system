import pandas as pd
import numpy as np
import os
# pyrefly: ignore [missing-import]
import joblib
from sklearn.metrics.pairwise import cosine_similarity
from app.data.loader import load_interaction_matrix

MODEL_DIR = os.path.join(os.path.dirname(__file__), '..', '..', 'artifacts')

def load_cbf_artifacts() -> dict:
    """Loads all the pre-calculated item feature matrices from Phase 3."""
    cbf_data = {}
    entities = ['hotel', 'location', 'vehicle', 'guide', 'shop_item']
    
    for entity in entities:
        path = os.path.join(MODEL_DIR, f'cbf_{entity}_features.joblib')
        if os.path.exists(path):
            data = joblib.load(path)
            
            # Unpack SciPy sparse matrices into standard NumPy dense arrays
            if hasattr(data['features'], 'toarray'):
                data['features'] = data['features'].toarray()
                
            cbf_data[f"{entity}s"] = data
            
    return cbf_data

def generate_training_data(interactions_df: pd.DataFrame) -> pd.DataFrame:
    print("Generating Features for the Hybrid Ranker...")
    
    # 1. Load CF Models and Reconstruct Predictions cleanly
    try:
        cf_mappings = joblib.load(os.path.join(MODEL_DIR, 'cf_mappings.joblib'))
        svd_model = joblib.load(os.path.join(MODEL_DIR, 'cf_svd_model.joblib'))
    except FileNotFoundError:
        raise Exception("CF models missing. Run Phase 2 first.")

    # Rebuild the user-item matrix to match SVD dimensions
    temp_df = interactions_df.copy()
    temp_df['unique_item_id'] = temp_df['item_type'] + '_' + temp_df['item_id'].astype(str)
    user_item_matrix = temp_df.pivot_table(
        index='user_id', 
        columns='unique_item_id', 
        values='implicit_score', 
        aggfunc='mean'
    ).fillna(0)

    # Clean matrix multiplication in latent space: (n_users, n_components) dot (n_components, n_items)
    user_latent = svd_model.transform(user_item_matrix)
    predicted_matrix = np.dot(user_latent, svd_model.components_)
    
    predicted_df = pd.DataFrame(
        predicted_matrix, 
        index=user_item_matrix.index, 
        columns=user_item_matrix.columns
    )
    
    # 2. Load CBF Models
    cbf_data = load_cbf_artifacts()
    
    # 3. Pre-compute mathematical User Profiles (Centroids) for CBF
    print("--> Calculating User Preference Vectors...")
    user_profiles = {}
    
    for user_id in interactions_df['user_id'].unique():
        user_profiles[user_id] = {}
        user_history = interactions_df[interactions_df['user_id'] == user_id]
        liked_items = user_history[user_history['implicit_score'] >= 4.0]
        
        for item_type in cbf_data.keys():
            type_likes = liked_items[liked_items['item_type'] == item_type]
            
            if not type_likes.empty:
                cbf_item_ids = cbf_data[item_type]['item_ids']
                cbf_features = cbf_data[item_type]['features']
                
                liked_ids = type_likes['item_id'].values
                idx = np.where(np.isin(cbf_item_ids, liked_ids))[0]
                
                if len(idx) > 0:
                    user_vector = cbf_features[idx].mean(axis=0)
                    user_profiles[user_id][item_type] = user_vector

    # 4. Generate the final Training DataFrame
    print("--> Synthesizing final ML matrices...")
    feature_rows = []
    
    for _, row in interactions_df.iterrows():
        user_id = row['user_id']
        item_type = row['item_type']
        item_id = row['item_id']
        actual_rating = row['implicit_score']
        
        # --- CALCULATE REAL CF SCORE ---
        unique_item_id = f"{item_type}_{item_id}"
        cf_score = 0.0
        
        if user_id in predicted_df.index and unique_item_id in predicted_df.columns:
            raw_score = predicted_df.loc[user_id, unique_item_id]
            cf_score = min(max(raw_score * 5.0, 0.0), 5.0) 
            
        # --- CALCULATE REAL CBF SCORE ---
        cbf_score = 0.5 # Fallback
        
        if item_type in cbf_data and item_type in user_profiles.get(user_id, {}):
            cbf_item_ids = cbf_data[item_type]['item_ids']
            idx = np.where(cbf_item_ids == item_id)[0]
            
            if len(idx) > 0:
                item_vector = cbf_data[item_type]['features'][idx[0]]
                user_vector = user_profiles[user_id][item_type]
                
                # Reshape the 1D arrays into 2D matrices for scikit-learn
                sim = cosine_similarity(
                    np.asarray(user_vector).reshape(1, -1), 
                    np.asarray(item_vector).reshape(1, -1)
                )
                cbf_score = sim[0][0]
                
        feature_rows.append({
            'cf_score': cf_score,
            'cbf_score': cbf_score,
            'actual_rating': actual_rating
        })
        
    print(f"✅ Generated {len(feature_rows)} mathematically authentic training rows.")
    return pd.DataFrame(feature_rows)