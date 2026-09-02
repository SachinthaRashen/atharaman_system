import pandas as pd
import numpy as np
import os
# pyrefly: ignore [missing-import]
import joblib
from sklearn.ensemble import RandomForestRegressor
from sklearn.model_selection import train_test_split
from sklearn.metrics import mean_squared_error

MODEL_PATH = os.path.join(os.path.dirname(__file__), '..', '..', 'artifacts', 'hybrid_rf_ranker.joblib')

def train_ranker(training_data: pd.DataFrame):
    """
    Trains the supervised Random Forest Ranker.
    Expected columns in training_data: ['cf_score', 'cbf_score', 'actual_rating']
    """
    print("Training Hybrid Random Forest Ranker...")
    
    # 1. Define our features (X) and our target (y)
    X = training_data[['cf_score', 'cbf_score']]
    y = training_data['actual_rating']
    
    # 2. Perform the Train/Test Split (80% training, 20% testing)
    X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)
    
    # 3. Initialize and train the Random Forest
    rf = RandomForestRegressor(n_estimators=100, random_state=42, n_jobs=-1)
    rf.fit(X_train, y_train)
    
    # 4. Test the model's accuracy on the 20% unseen data
    predictions = rf.predict(X_test)
    rmse = np.sqrt(mean_squared_error(y_test, predictions))
    
    print(f"📊 Ranker Evaluation Complete!")
    print(f"   --> Root Mean Square Error (RMSE): {rmse:.4f}")
    
    # 5. Save the compiled Ranker
    os.makedirs(os.path.dirname(MODEL_PATH), exist_ok=True)
    joblib.dump(rf, MODEL_PATH)
    print(f"✅ Hybrid Ranker saved successfully to {MODEL_PATH}")
    
    return rf

def rank_candidates(user_id: int, db_candidates: list[dict]) -> list[dict]:
    """
    Called by FastAPI in production.
    db_candidates format: [{'item_type': 'hotels', 'item_id': 12}, ...]
    Note: Spatial filtering is handled by PostGIS prior to this step.
    """
    # 1. Load the trained Ranker
    if not os.path.exists(MODEL_PATH):
        raise FileNotFoundError("Hybrid Ranker model not found. Please train the pipeline first.")
    
    rf = joblib.load(MODEL_PATH)
    
    # 2. Extract ML scores for the candidates
    feature_rows = []
    
    for candidate in db_candidates:
        # MOCK SCORES for the architecture skeleton:
        # When building the FastAPI endpoints, these will be dynamically calculated
        # using the user's live profile vector and the SVD matrix.
        cf_score = 4.0   
        cbf_score = 0.85 
        
        feature_rows.append({
            'cf_score': cf_score,
            'cbf_score': cbf_score
        })
        
    X_inference = pd.DataFrame(feature_rows)
    
    # 3. Predict the final 1-5 star ratings
    predicted_ratings = rf.predict(X_inference)
    
    # 4. Attach the predictions back to the candidates and sort them
    for i, candidate in enumerate(db_candidates):
        candidate['final_score'] = predicted_ratings[i]
        
    # Sort highest score first
    ranked_candidates = sorted(db_candidates, key=lambda x: x['final_score'], reverse=True)
    
    return ranked_candidates