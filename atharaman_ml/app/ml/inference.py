import numpy as np
import pandas as pd

from app.ml.cbf import (
    calculate_cbf_score,
    normalize_entity_type
)
from app.ml.cf import (
    predict_user_item_scores,
    make_unique_item_id
)
from app.ml.kmeans import predict_cluster


def _get_general_rating_lookup(interactions_df: pd.DataFrame) -> dict:
    rated = interactions_df[interactions_df["rating"].notna()].copy()

    if rated.empty:
        return {}

    rated["normalized_item_type"] = rated["item_type"].astype(str).apply(normalize_entity_type)
    grouped = rated.groupby(["normalized_item_type", "item_id"])["rating"].mean()

    return {
        (item_type, int(item_id)): float(value)
        for (item_type, item_id), value in grouped.items()
    }


def _get_candidate_price(candidate: dict):
    item_type = normalize_entity_type(candidate.get("item_type", ""))

    if item_type == "guides":
        return candidate.get("daily_rate")
    if item_type == "vehicles":
        return candidate.get("rate_per_day") or candidate.get("rate_per_km")
    if item_type == "shop_items":
        return candidate.get("rental_price_per_day")
    
    return None


def _has_user_history(user_id: int, interactions_df: pd.DataFrame) -> bool:
    return bool((interactions_df["user_id"] == user_id).any())


def rank_live_candidates(
    user: dict,
    candidates: list[dict],
    interactions_df: pd.DataFrame,
    selected_location: dict | None = None
) -> list[dict]:

    if not candidates:
        return []

    user_id = int(user["user_id"])
    has_history = _has_user_history(user_id, interactions_df)

    # -----------------------------------------------------------------------
    # Collaborative Filtering & Persona Assignment
    # -----------------------------------------------------------------------
    cluster_id = None

    if has_history:
        cf_scores = predict_user_item_scores(user_id, interactions_df)
    else:
        cf_scores = {}
        # FIXED: Fetch the K-Means persona AND their favorite location types
        cluster_id, cluster_prefs = predict_cluster(user)
        user["assigned_persona_cluster"] = cluster_id
        user["persona_preferred_locations"] = cluster_prefs

    # -----------------------------------------------------------------------
    # General ratings & Prices
    # -----------------------------------------------------------------------
    rating_lookup = _get_general_rating_lookup(interactions_df)

    candidate_prices = [_get_candidate_price(candidate) for candidate in candidates]
    candidate_prices = [price for price in candidate_prices if price is not None]

    ranked = []

    for candidate in candidates:
        item_type = normalize_entity_type(candidate["item_type"])
        item_id = int(candidate["item_id"])

        # CF Score
        unique_item = make_unique_item_id(item_type, item_id)
        cf_score = float(cf_scores.get(unique_item, 0.0))
        cf_normalized = float(np.clip(cf_score / 5.0, 0.0, 1.0))

        # CBF Score
        cbf_score = calculate_cbf_score(
            user=user,
            candidate=candidate,
            selected_location=selected_location,
            candidate_prices=candidate_prices
        )

        # General Rating
        entity_rating = float(rating_lookup.get((item_type, item_id), 3.0))
        rating_normalized = float(np.clip(entity_rating / 5.0, 0.0, 1.0))

        # -------------------------------------------------------------------
        # Final fusion
        # -------------------------------------------------------------------
        if has_history:
            # Existing tourist: Previous behaviour is the majority signal.
            final_normalized = (cf_normalized * 0.60) + (cbf_score * 0.30) + (rating_normalized * 0.10)
        else:
            # Cold-start tourist: CBF/persona-style compatibility dominates.
            final_normalized = (cbf_score * 0.90) + (rating_normalized * 0.10)

        result = dict(candidate)
        result["cf_score"] = cf_score
        result["cbf_score"] = float(cbf_score)
        result["entity_rating"] = entity_rating
        
        # Expose the cluster matching ID for frontend debugging/transparency
        if cluster_id is not None:
            result["matched_cluster"] = cluster_id

        result["final_score"] = float(np.clip(final_normalized * 5.0, 0.0, 5.0))
        ranked.append(result)

    ranked.sort(key=lambda value: value["final_score"], reverse=True)
    return ranked