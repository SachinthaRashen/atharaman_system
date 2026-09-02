import pandas as pd
from app.core.database import engine

def load_tourist_features() -> pd.DataFrame:
    """Loads tourists for K-Means demographic clustering."""
    query = """
    SELECT 
        id AS user_id,
        country,
        native_language,
        preferred_travel_style,
        preferred_budget_tier,
        prefers_guided_tours,
        requires_accessibility,
        EXTRACT(YEAR FROM AGE(CURRENT_DATE, date_of_birth)) AS age
    FROM users
    WHERE role = 'tourist';
    """
    return pd.read_sql(query, engine)

def load_interaction_matrix() -> pd.DataFrame:
    """Combines explicit reviews and implicit interactions into a unified matrix."""
    query = """
    -- Explicit Reviews (Scale 1-5)
    SELECT 
        user_id,
        reviewable_id AS item_id,
        reviewable_type AS item_type,
        rating::float AS implicit_score,
        'review' AS source
    FROM reviews

    UNION ALL

    -- Implicit Interactions (Weighted)
    SELECT 
        user_id,
        interactable_id AS item_id,
        interactable_type AS item_type,
        CASE 
            WHEN interaction_type = 'phone_revealed' THEN 5.0
            WHEN interaction_type = 'whatsapp_clicked' THEN 4.0
            WHEN interaction_type = 'email_clicked' THEN 2.5
            WHEN interaction_type = 'bookmarked' THEN 1.5
            ELSE 1.0
        END AS implicit_score,
        'interaction' AS source
    FROM user_interactions;
    """
    return pd.read_sql(query, engine)

def load_service_entities() -> dict[str, pd.DataFrame]:
    """Loads all vendors/items for Content-Based Filtering."""
    return {
        "hotels": pd.read_sql("SELECT id, hotel_name, budget_tier, base_price, pricing_model FROM hotels;", engine),
        "vehicles": pd.read_sql("SELECT id, vehicle_type, rate_per_day, rate_per_km, pricing_model FROM vehicles;", engine),
        "guides": pd.read_sql("SELECT id, daily_rate, languages_spoken FROM guides;", engine),
        "locations": pd.read_sql("SELECT id, name, location_type, terrain_difficulty, requires_4x4, requires_guide FROM locations;", engine)
    }