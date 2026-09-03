import numpy as np
import pandas as pd
import math
from app.data.loader import load_all_ml_data
from app.ml.inference import rank_live_candidates
from sklearn.metrics import mean_squared_error, mean_absolute_error

def calculate_ndcg(relevant_items, ranked_items, k=5):
    """Calculates Normalized Discounted Cumulative Gain at K."""
    dcg = 0.0
    idcg = 0.0
    
    # Calculate DCG
    for i in range(min(k, len(ranked_items))):
        if ranked_items[i] in relevant_items:
            dcg += 1.0 / math.log2(i + 2) # i+2 because rank starts at 1, log2(1) is 0
            
    # Calculate IDCG (Ideal DCG)
    for i in range(min(k, len(relevant_items))):
        idcg += 1.0 / math.log2(i + 2)
        
    return dcg / idcg if idcg > 0 else 0.0

def calculate_precision_at_k(relevant_items, ranked_items, k=5):
    """Calculates Precision at K."""
    if not relevant_items or k == 0:
        return 0.0
    top_k = ranked_items[:k]
    hits = sum(1 for item in top_k if item in relevant_items)
    return hits / k

def run_evaluation():
    print("=" * 70)
    print("ATHARAMAN SYSTEM EVALUATION (IR & ACCURACY METRICS)")
    print("=" * 70)
    
    print("\nLoading models and database matrices...")
    tourists_df, interactions_df, services = load_all_ml_data()
    
    hotels_df = services['hotels'].copy()
    hotels_df['item_type'] = 'hotels'
    hotels_df['item_id'] = hotels_df['id']
    
    # Convert hotel candidates to dictionaries for the inference engine
    all_hotel_candidates = hotels_df.to_dict('records')
    
    # Filter for explicit hotel ratings to test against ground truth
    hotel_ratings = interactions_df[
        (interactions_df['item_type'] == 'hotels') & 
        (interactions_df['rating'].notna())
    ]
    
    if hotel_ratings.empty:
        print("\n❌ Not enough explicit hotel ratings to perform evaluation.")
        return

    users_to_evaluate = hotel_ratings['user_id'].unique()
    
    actual_scores = []
    predicted_scores = []
    
    precision_scores = []
    ndcg_scores = []
    
    print(f"Evaluating Fusion Engine across {len(users_to_evaluate)} active tourists...\n")
    
    for user_id in users_to_evaluate:
        # 1. Build the User Profile
        user_row = tourists_df[tourists_df['user_id'] == user_id]
        if user_row.empty:
            continue
            
        user_dict = user_row.iloc[0].to_dict()
        # Handle NaN ages
        if pd.isna(user_dict.get('age')):
            user_dict['age'] = 30.0
            
        # 2. Get ground truth for this user
        user_hotel_history = hotel_ratings[hotel_ratings['user_id'] == user_id]
        
        # Items they actually liked (4 or 5 stars)
        relevant_hotels = set(
            user_hotel_history[user_hotel_history['rating'] >= 4.0]['item_id'].tolist()
        )
        
        # 3. Run the Inference Engine
        ranked_results = rank_live_candidates(
            user=user_dict,
            candidates=all_hotel_candidates,
            interactions_df=interactions_df,
            selected_location=None # Hotels don't strictly require spatial context for CBF
        )
        
        # Extract the ranked order of item IDs
        ranked_item_ids = [result['item_id'] for result in ranked_results]
        
        # 4. Calculate IR Metrics (if they had relevant items)
        if relevant_hotels:
            precision_scores.append(calculate_precision_at_k(relevant_hotels, ranked_item_ids, k=5))
            ndcg_scores.append(calculate_ndcg(relevant_hotels, ranked_item_ids, k=5))
            
        # 5. Calculate Accuracy (RMSE/MAE)
        for _, row in user_hotel_history.iterrows():
            actual_rating = row['rating']
            item_id = row['item_id']
            
            # Find what the engine predicted for this specific item
            for result in ranked_results:
                if result['item_id'] == item_id:
                    actual_scores.append(actual_rating)
                    predicted_scores.append(result['final_score'])
                    break

    # --- FINAL CALCULATIONS ---
    rmse = np.sqrt(mean_squared_error(actual_scores, predicted_scores))
    mae = mean_absolute_error(actual_scores, predicted_scores)
    
    avg_precision = np.mean(precision_scores) if precision_scores else 0.0
    avg_ndcg = np.mean(ndcg_scores) if ndcg_scores else 0.0

    print("📊 SYSTEM PERFORMANCE RESULTS:")
    print("-" * 35)
    print("1. Rating Accuracy Metrics")
    print(f"   Root Mean Square Error (RMSE): {rmse:.4f}")
    print(f"   Mean Absolute Error (MAE):     {mae:.4f}")
    print("   *(Lower is better. Measures how close predictions are to actual 1-5 ratings)*\n")
    
    print("2. Ranking Quality Metrics (Top-5)")
    print(f"   Precision@5: {avg_precision:.4f}")
    print(f"   NDCG@5:      {avg_ndcg:.4f}")
    print("   *(Higher is better, max 1.0. Measures if highly-rated items appear at the very top)*")
    print("-" * 35)
    
if __name__ == "__main__":
    run_evaluation()