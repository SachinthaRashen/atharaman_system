import pandas as pd
import os
# pyrefly: ignore [missing-import]
import joblib
from sklearn.cluster import KMeans
from sklearn.preprocessing import StandardScaler, OneHotEncoder
from sklearn.compose import ColumnTransformer
from sklearn.pipeline import Pipeline

# Path to save our trained model inside the artifacts folder
MODEL_PATH = os.path.join(os.path.dirname(__file__), '..', '..', 'artifacts', 'tourist_kmeans_model.joblib')

def build_clustering_pipeline() -> Pipeline:
    """Builds the transformation and clustering pipeline."""
    
    # Define which columns need which mathematical transformation
    categorical_features = ['preferred_travel_style', 'preferred_budget_tier', 'prefers_guided_tours', 'requires_accessibility']
    numeric_features = ['age']

    # Create the transformers
    preprocessor = ColumnTransformer(
        transformers=[
            ('num', StandardScaler(), numeric_features),
            ('cat', OneHotEncoder(handle_unknown='ignore'), categorical_features)
        ]
    )

    # We will group tourists into 5 distinct personas (clusters)
    pipeline = Pipeline(steps=[
        ('preprocessor', preprocessor),
        ('clusterer', KMeans(n_clusters=5, random_state=42, n_init='auto'))
    ])
    
    return pipeline

def train_and_save_model(tourists_df: pd.DataFrame):
    """Trains the K-Means model on the database users and saves it to disk."""
    print("Training K-Means Clustering Model...")
    
    # Drop rows missing critical data to prevent training errors
    train_data = tourists_df.dropna(subset=['preferred_travel_style', 'preferred_budget_tier', 'age'])
    
    # Initialize and train the pipeline
    pipeline = build_clustering_pipeline()
    pipeline.fit(train_data)
    
    # Ensure artifacts directory exists
    os.makedirs(os.path.dirname(MODEL_PATH), exist_ok=True)
    
    # Save the trained model to disk so FastAPI can load it instantly
    joblib.dump(pipeline, MODEL_PATH)
    print(f"✅ Model saved successfully to {MODEL_PATH}")
    
    return pipeline