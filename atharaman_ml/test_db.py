import pandas as pd
from app.data.loader import load_tourist_features, load_service_entities

def test_pipeline():
    print("Testing PostgreSQL Connection...")
    
    try:
        # 1. Fetch the tourists
        tourists_df = load_tourist_features()
        print(f"\n✅ Successfully loaded {len(tourists_df)} tourists.")
        print("Sample Tourist Data:")
        print(tourists_df[['user_id', 'preferred_travel_style', 'preferred_budget_tier']].head(3))
        
        # 2. Fetch the vendors (Hotels)
        services = load_service_entities()
        hotels_df = services['hotels']
        print(f"\n✅ Successfully loaded {len(hotels_df)} hotels.")
        print("Sample Hotel Data:")
        print(hotels_df[['hotel_name', 'budget_tier', 'base_price']].head(3))
        
    except Exception as e:
        print(f"\n❌ Connection Failed: {e}")

if __name__ == "__main__":
    test_pipeline()