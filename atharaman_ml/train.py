from app.data.loader import load_all_ml_data
from app.ml.kmeans import train_and_save_model
from app.ml.cf import train_svd_model

def main():
    print("=" * 70)
    print("ATHARAMAN MACHINE LEARNING TRAINING PIPELINE")
    print("=" * 70)

    # Properly unpack the tuple returned by loader.py
    tourists_df, interactions_df, services = load_all_ml_data()

    print("\n[1/3] Data loaded.")

    print("\n[2/3] Training K-Means...")
    train_and_save_model(
        tourists_df, 
        interactions_df, 
        services
    )

    print("\n[3/3] Training SVD...")
    train_svd_model(interactions_df)

    print("\n" + "=" * 70)
    print("ATHARAMAN ML TRAINING COMPLETE")
    print("=" * 70)

if __name__ == "__main__":
    main()