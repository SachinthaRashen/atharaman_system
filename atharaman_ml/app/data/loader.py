import pandas as pd
from app.core.database import engine


# ---------------------------------------------------------------------------
# Constants
# ---------------------------------------------------------------------------

INTERACTION_WEIGHTS = {
    "phone_revealed": 5.0,
    "whatsapp_clicked": 4.0,
    "email_clicked": 2.5,
    "bookmarked": 1.5,
}


# ---------------------------------------------------------------------------
# Tourists
# ---------------------------------------------------------------------------

def load_tourist_features() -> pd.DataFrame:
    """
    Load tourist profiles used by K-Means and recommendation inference.

    Country and native_language are intentionally retained in the dataframe
    because they can be used later for contextual matching (especially guide
    language matching), but they are NOT used by the K-Means feature set.
    """

    query = """
    SELECT
        id AS user_id,
        country,
        native_language,
        preferred_travel_style,
        preferred_budget_tier,
        prefers_guided_tours,
        requires_accessibility,
        EXTRACT(
            YEAR FROM AGE(CURRENT_DATE, date_of_birth)
        )::float AS age
    FROM users
    WHERE role = 'tourist';
    """

    return pd.read_sql(query, engine)


# ---------------------------------------------------------------------------
# Interactions + Reviews
# ---------------------------------------------------------------------------

def load_interaction_matrix() -> pd.DataFrame:
    """
    Load historical reviews and implicit interactions.

    Returned dataframe contains:

        user_id
        item_id
        item_type
        rating
        interaction_score
        preference_score
        source

    Important:
        - rating is ONLY the actual explicit 1-5 rating.
        - interaction_score is ONLY the implicit engagement weight.
        - preference_score is the signal used by CF/CBF.
        - No interaction means there is simply no row. It is NOT dislike.
    """

    query = """
    SELECT
        user_id,
        reviewable_id AS item_id,

        CASE
            WHEN reviewable_type = 'App\\Models\\Hotel'
                THEN 'hotels'
            WHEN reviewable_type = 'App\\Models\\Location'
                THEN 'locations'
            WHEN reviewable_type = 'App\\Models\\Vehicle'
                THEN 'vehicles'
            WHEN reviewable_type = 'App\\Models\\Guide'
                THEN 'guides'
            WHEN reviewable_type = 'App\\Models\\ShopItem'
                THEN 'shop_items'
            WHEN reviewable_type = 'App\\Models\\Shop'
                THEN 'shops'
            ELSE 'unknown'
        END AS item_type,

        rating::float AS rating,
        NULL::float AS interaction_score,
        rating::float AS preference_score,

        'review' AS source

    FROM reviews

    UNION ALL

    SELECT
        user_id,
        interactable_id AS item_id,

        CASE
            WHEN interactable_type = 'App\\Models\\Hotel'
                THEN 'hotels'
            WHEN interactable_type = 'App\\Models\\Location'
                THEN 'locations'
            WHEN interactable_type = 'App\\Models\\Vehicle'
                THEN 'vehicles'
            WHEN interactable_type = 'App\\Models\\Guide'
                THEN 'guides'
            WHEN interactable_type = 'App\\Models\\ShopItem'
                THEN 'shop_items'
            WHEN interactable_type = 'App\\Models\\Shop'
                THEN 'shops'
            ELSE 'unknown'
        END AS item_type,

        NULL::float AS rating,

        CASE
            WHEN interaction_type = 'phone_revealed'
                THEN 5.0
            WHEN interaction_type = 'whatsapp_clicked'
                THEN 4.0
            WHEN interaction_type = 'email_clicked'
                THEN 2.5
            WHEN interaction_type = 'bookmarked'
                THEN 1.5
            ELSE 1.0
        END AS interaction_score,

        CASE
            WHEN interaction_type = 'phone_revealed'
                THEN 5.0
            WHEN interaction_type = 'whatsapp_clicked'
                THEN 4.0
            WHEN interaction_type = 'email_clicked'
                THEN 2.5
            WHEN interaction_type = 'bookmarked'
                THEN 1.5
            ELSE 1.0
        END AS preference_score,

        'interaction' AS source

    FROM user_interactions;
    """

    df = pd.read_sql(query, engine)

    df = df[df["item_type"] != "unknown"].copy()

    if df.empty:
        return df

    # Keep actual ratings untouched.
    df["rating"] = pd.to_numeric(df["rating"], errors="coerce")
    df["interaction_score"] = pd.to_numeric(
        df["interaction_score"],
        errors="coerce"
    )
    df["preference_score"] = pd.to_numeric(
        df["preference_score"],
        errors="coerce"
    )

    return df


# ---------------------------------------------------------------------------
# Service entities
# ---------------------------------------------------------------------------

def load_service_entities() -> dict[str, pd.DataFrame]:
    """
    Load all recommendation entities with the attributes needed by:
        - CBF (Content-Based Filtering constraints and scoring)
        - PostGIS/location-aware inference
    """

    return {
        "hotels": pd.read_sql(
            """
            SELECT
                id,
                hotel_name,
                budget_tier,
                pricing_model,
                base_price,
                max_total_capacity,
                is_wheelchair_accessible,
                ST_Y(coordinates::geometry) AS latitude,
                ST_X(coordinates::geometry) AS longitude
            FROM hotels;
            """,
            engine,
        ),

        "vehicles": pd.read_sql(
            """
            SELECT
                id,
                vehicle_make_model,
                vehicle_type,
                rental_type,
                terrain_capability,
                pricing_model,
                rate_per_day,
                rate_per_km,
                passenger_capacity,
                ST_Y(coordinates::geometry) AS latitude,
                ST_X(coordinates::geometry) AS longitude
            FROM vehicles;
            """,
            engine,
        ),

        "guides": pd.read_sql(
            """
            SELECT
                id,
                specialty,
                languages_spoken,
                daily_rate,
                experience_years,
                ST_Y(coordinates::geometry) AS latitude,
                ST_X(coordinates::geometry) AS longitude
            FROM guides;
            """,
            engine,
        ),

        "locations": pd.read_sql(
            """
            SELECT
                id,
                name,
                location_type,
                terrain_difficulty,
                requires_4x4,
                requires_guide,
                elevation_meters,
                ST_Y(coordinates::geometry) AS latitude,
                ST_X(coordinates::geometry) AS longitude
            FROM locations;
            """,
            engine,
        ),

        "shop_items": pd.read_sql(
            """
            SELECT
                id,
                shop_id,
                item_name,
                item_category,
                rental_price_per_day,
                stock_quantity
            FROM shop_items;
            """,
            engine,
        ),

        "shops": pd.read_sql(
            """
            SELECT
                id,
                shop_name,
                ST_Y(coordinates::geometry) AS latitude,
                ST_X(coordinates::geometry) AS longitude
            FROM shops;
            """,
            engine,
        ),
    }


# ---------------------------------------------------------------------------
# Helpers
# ---------------------------------------------------------------------------

def load_all_ml_data():
    """
    Convenience loader used by the training orchestrator.
    """

    tourists = load_tourist_features()
    interactions = load_interaction_matrix()
    services = load_service_entities()

    return tourists, interactions, services