import pandas as pd
import os
# pyrefly: ignore [missing-import]
import joblib
from sklearn.preprocessing import StandardScaler, OneHotEncoder
from sklearn.compose import ColumnTransformer

MODEL_DIR = os.path.join(os.path.dirname(__file__), '..', '..', 'artifacts')

def train_cbf_models(services: dict[str, pd.DataFrame]):
    print("Training Content-Based Filtering (CBF) Models...")
    os.makedirs(MODEL_DIR, exist_ok=True)
    
    # 1. Process Hotels
    if 'hotels' in services and not services['hotels'].empty:
        df = services['hotels'].copy()
        df.fillna(0, inplace=True)  # FIX: Neutralize NULLs
        
        preprocessor = ColumnTransformer([
            ('num', StandardScaler(), ['base_price']),
            ('cat', OneHotEncoder(handle_unknown='ignore'), ['budget_tier', 'pricing_model'])
        ])
        features = preprocessor.fit_transform(df)
        joblib.dump(preprocessor, os.path.join(MODEL_DIR, 'cbf_hotel_preprocessor.joblib'))
        joblib.dump({'item_ids': df['id'].values, 'features': features}, os.path.join(MODEL_DIR, 'cbf_hotel_features.joblib'))

    # 2. Process Locations
    if 'locations' in services and not services['locations'].empty:
        df = services['locations'].copy()
        df.fillna(0, inplace=True)
        
        preprocessor = ColumnTransformer([
            ('cat', OneHotEncoder(handle_unknown='ignore'), ['location_type', 'terrain_difficulty', 'requires_4x4', 'requires_guide'])
        ])
        features = preprocessor.fit_transform(df)
        joblib.dump(preprocessor, os.path.join(MODEL_DIR, 'cbf_location_preprocessor.joblib'))
        joblib.dump({'item_ids': df['id'].values, 'features': features}, os.path.join(MODEL_DIR, 'cbf_location_features.joblib'))

    # 3. Process Vehicles
    if 'vehicles' in services and not services['vehicles'].empty:
        df = services['vehicles'].copy()
        df.fillna(0, inplace=True)  # This saves the pipeline from NULL rate_per_day / rate_per_km
        
        preprocessor = ColumnTransformer([
            ('num', StandardScaler(), ['rate_per_day', 'rate_per_km']),
            ('cat', OneHotEncoder(handle_unknown='ignore'), ['vehicle_type', 'pricing_model'])
        ])
        features = preprocessor.fit_transform(df)
        joblib.dump(preprocessor, os.path.join(MODEL_DIR, 'cbf_vehicle_preprocessor.joblib'))
        joblib.dump({'item_ids': df['id'].values, 'features': features}, os.path.join(MODEL_DIR, 'cbf_vehicle_features.joblib'))

    # 4. Process Guides
    if 'guides' in services and not services['guides'].empty:
        df = services['guides'].copy()
        
        # Handle the JSON list conversion safely
        df['languages_spoken'] = df['languages_spoken'].apply(
            lambda x: ','.join(x) if isinstance(x, list) else str(x)
        )
        df.fillna(0, inplace=True)
        
        preprocessor = ColumnTransformer([
            ('num', StandardScaler(), ['daily_rate']),
            ('cat', OneHotEncoder(handle_unknown='ignore'), ['languages_spoken'])
        ])
        features = preprocessor.fit_transform(df)
        joblib.dump(preprocessor, os.path.join(MODEL_DIR, 'cbf_guide_preprocessor.joblib'))
        joblib.dump({'item_ids': df['id'].values, 'features': features}, os.path.join(MODEL_DIR, 'cbf_guide_features.joblib'))

    # 5. Process Shop Items
    if 'shop_items' in services and not services['shop_items'].empty:
        df = services['shop_items'].copy()
        df.fillna(0, inplace=True)
        
        preprocessor = ColumnTransformer([
            ('num', StandardScaler(), ['rental_price_per_day']), 
            ('cat', OneHotEncoder(handle_unknown='ignore'), ['item_category']) 
        ])
        features = preprocessor.fit_transform(df)
        joblib.dump(preprocessor, os.path.join(MODEL_DIR, 'cbf_shop_item_preprocessor.joblib'))
        joblib.dump({'item_ids': df['id'].values, 'features': features}, os.path.join(MODEL_DIR, 'cbf_shop_item_features.joblib'))

    print("✅ All CBF features compiled and saved successfully.")