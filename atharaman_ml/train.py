from app.data.loader import load_tourist_features, load_interaction_matrix, load_service_entities
from app.ml.kmeans import train_and_save_model
from app.ml.cf import train_svd_model
from app.ml.cbf import train_cbf_models
# pyrefly: ignore [missing-import]
from app.ml.generator import generate_training_data
from app.ml.ranker import train_ranker

def execute_pipeline():
    print("Initializing Machine Learning Training Pipeline...\n")
    
    try:
        print("--> Fetching tourist data from database...")
        tourists_df = load_tourist_features()
        train_and_save_model(tourists_df)
        
        print("\n--> Fetching interaction data from database...")
        interactions_df = load_interaction_matrix()
        train_svd_model(interactions_df)
        
        print("\n--> Fetching service entities from database...")
        services = load_service_entities()
        train_cbf_models(services)
        
        # --- PHASE 4: The Hybrid Ranker ---
        print("\n--> Initiating Phase 4: Hybrid Ranker...")
        training_features = generate_training_data(interactions_df)
        train_ranker(training_features)
        
        print("\n🎉 ALL PHASES COMPLETE: Full Recommendation Engine is ready for production.")
        
    except Exception as e:
        print(f"\n❌ Pipeline Failed: {e}")

if __name__ == "__main__":
    execute_pipeline()