import numpy as np

# ---------------------------------------------------------------------------
# Location mappings
# ---------------------------------------------------------------------------

TRAVEL_STYLE_LOCATION_TYPES = {
    "adventure": {"mountain_trek", "waterfall", "campsite", "beach_coastal"},
    "cultural_historic": {"ancient_ruins", "religious_site", "urban_city"},
    "nature_wildlife": {"wildlife_safari", "rainforest", "botanical_garden"},
    "leisure_wellness": {"beach_coastal", "tea_estate", "lake_reservoir"}
}

TRAVEL_STYLE_TERRAIN = {
    "adventure": {"moderate", "challenging", "extreme"},
    "nature_wildlife": {"moderate", "challenging"},
    "cultural_historic": {"easy", "moderate"},
    "leisure_wellness": {"easy", "moderate"}
}

# ---------------------------------------------------------------------------
# Guide speciality mappings
# ---------------------------------------------------------------------------

LOCATION_GUIDE_SPECIALTIES = {
    "mountain_trek": {"trek", "hiking", "mountain", "adventure"},
    "waterfall": {"nature", "hiking", "adventure"},
    "wildlife_safari": {"wildlife", "nature", "safari"},
    "rainforest": {"nature", "wildlife", "eco"},
    "ancient_ruins": {"history", "culture", "archaeology"},
    "religious_site": {"culture", "history", "religion"},
    "beach_coastal": {"marine", "beach", "surf"}
}

# ---------------------------------------------------------------------------
# Shop category mappings
# ---------------------------------------------------------------------------

LOCATION_SHOP_CATEGORIES = {
    "mountain_trek": {"hiking_trekking", "camping_gear"},
    "waterfall": {"hiking_trekking", "water_sports"},
    "campsite": {"camping_gear", "hiking_trekking"},
    "wildlife_safari": {"camping_gear", "general_travel"},
    "rainforest": {"hiking_trekking", "camping_gear"},
    "beach_coastal": {"water_sports", "general_travel"},
    "ancient_ruins": {"general_travel"},
    "religious_site": {"general_travel"}
}

# ---------------------------------------------------------------------------
# Helpers
# ---------------------------------------------------------------------------

def normalize_entity_type(item_type: str) -> str:
    mapping = {
        "hotel": "hotels", "hotels": "hotels",
        "guide": "guides", "guides": "guides",
        "vehicle": "vehicles", "vehicles": "vehicles",
        "location": "locations", "locations": "locations",
        "shop_item": "shop_items", "shop_items": "shop_items",
    }
    value = str(item_type).strip().lower()
    return mapping.get(value, value)

def _language_match(user_language, languages) -> float:
    if not user_language:
        return 0.0

    user_language = str(user_language).strip().lower()

    if isinstance(languages, list):
        values = languages
    elif isinstance(languages, str):
        values = languages.replace("{", "").replace("}", "").replace('"', "").replace("|", ",").split(",")
    else:
        values = []

    values = [str(value).strip().lower() for value in values]

    if user_language in values:
        return 1.0
    if "english" in values:
        return 0.5

    return 0.0

def _price_compatibility(price, preferred_budget_tier, candidate_prices) -> float:
    if price is None:
        return 0.5

    valid_prices = [float(value) for value in candidate_prices if value is not None and float(value) > 0]

    if len(valid_prices) < 3:
        return 0.5

    price = float(price)
    low = float(np.percentile(valid_prices, 33))
    high = float(np.percentile(valid_prices, 66))
    budget = str(preferred_budget_tier or "mid_range")

    if budget == "budget":
        if price <= low: return 1.0
        if price <= high: return 0.5
        return 0.0

    if budget == "luxury":
        if price >= high: return 1.0
        if price >= low: return 0.7
        return 0.4

    if low <= price <= high:
        return 1.0

    return 0.5

# ---------------------------------------------------------------------------
# LOCATION CBF
# ---------------------------------------------------------------------------

def _location_score(user: dict, candidate: dict) -> float:
    travel_style = str(user.get("preferred_travel_style", "adventure"))
    location_type = candidate.get("location_type")
    terrain = candidate.get("terrain_difficulty")

    type_match = float(location_type in TRAVEL_STYLE_LOCATION_TYPES.get(travel_style, set()))
    terrain_match = float(terrain in TRAVEL_STYLE_TERRAIN.get(travel_style, set()))

    # --- THE K-MEANS BRIDGE ---
    persona_match = 0.0
    persona_favorites = user.get("persona_preferred_locations", [])
    if location_type in persona_favorites:
        persona_match = 1.0             # The ML model proves this cluster loves this location type

    elevation = float(candidate.get("elevation_meters") or 0)
    elevation_match = 0.5

    if travel_style == "adventure":
        elevation_match = min(elevation / 2000.0, 1.0)
    elif travel_style == "nature_wildlife":
        elevation_match = min(elevation / 2500.0, 1.0)

    return float((type_match * 0.50) + (persona_match * 0.30) + (terrain_match * 0.10) + (elevation_match * 0.10))

# ---------------------------------------------------------------------------
# HOTEL CBF
# ---------------------------------------------------------------------------

def _hotel_score(user: dict, candidate: dict) -> float:
    requires_accessibility = bool(user.get("requires_accessibility", False))
    
    # Hard constraint: Disqualify immediately if accessibility is required but not met
    if requires_accessibility:
        is_accessible = bool(candidate.get("is_wheelchair_accessible", False))
        if not is_accessible:
            return 0.0

    preferred_budget = user.get("preferred_budget_tier", "mid_range")
    budget_match = float(candidate.get("budget_tier") == preferred_budget)
    
    return float(budget_match * 1.0)

# ---------------------------------------------------------------------------
# GUIDE CBF
# ---------------------------------------------------------------------------

def _guide_score(user: dict, candidate: dict, selected_location: dict | None, candidate_prices) -> float:
    if not selected_location:
        return 0.0

    location_type = selected_location.get("location_type")
    specialty = str(candidate.get("specialty", "")).lower()

    terms = LOCATION_GUIDE_SPECIALTIES.get(location_type, set())
    specialty_match = float(any(term in specialty for term in terms))

    language_match = _language_match(user.get("native_language", "English"), candidate.get("languages_spoken"))
    price_match = _price_compatibility(candidate.get("daily_rate"), user.get("preferred_budget_tier"), candidate_prices)

    location_requires_guide = bool(selected_location.get("requires_guide", False))
    user_prefers_guide = bool(user.get("prefers_guided_tours", False))
    guide_need_match = float(location_requires_guide or user_prefers_guide)

    return float((specialty_match * 0.40) + (language_match * 0.30) + (price_match * 0.20) + (guide_need_match * 0.10))

# ---------------------------------------------------------------------------
# VEHICLE CBF
# ---------------------------------------------------------------------------

def _vehicle_score(user: dict, candidate: dict, selected_location: dict | None, candidate_prices) -> float:
    if not selected_location:
        return 0.0

    terrain = selected_location.get("terrain_difficulty")
    requires_4x4 = bool(selected_location.get("requires_4x4", False))
    capability = candidate.get("terrain_capability")
    vehicle_type = candidate.get("vehicle_type")

    # Hard constraint: Disqualify immediately if 4x4 is required but not met
    if requires_4x4 and capability != "off_road_4x4":
        return 0.0

    terrain_match = 1.0
    vehicle_type_match = 1.0

    if terrain in {"challenging", "extreme"}:
        vehicle_type_match = float(vehicle_type in {"safari_jeep", "suv_4x4", "motorbike"})

    price = candidate.get("rate_per_day") or candidate.get("rate_per_km")
    price_match = _price_compatibility(price, user.get("preferred_budget_tier"), candidate_prices)

    return float((terrain_match * 0.45) + (vehicle_type_match * 0.25) + (price_match * 0.30))

# ---------------------------------------------------------------------------
# SHOP ITEM CBF
# ---------------------------------------------------------------------------

def _shop_item_score(user: dict, candidate: dict, selected_location: dict | None, candidate_prices) -> float:
    if not selected_location:
        return 0.0

    location_type = selected_location.get("location_type")
    category = candidate.get("item_category")

    suitable_categories = LOCATION_SHOP_CATEGORIES.get(location_type, set())
    category_match = float(category in suitable_categories)

    elevation = float(selected_location.get("elevation_meters") or 0)
    elevation_match = 0.5

    if elevation >= 1500:
        elevation_match = float(category in {"hiking_trekking", "camping_gear", "general_travel"})

    price_match = _price_compatibility(candidate.get("rental_price_per_day"), user.get("preferred_budget_tier"), candidate_prices)

    return float((category_match * 0.50) + (elevation_match * 0.10) + (price_match * 0.40))

# ---------------------------------------------------------------------------
# Public CBF function
# ---------------------------------------------------------------------------

def calculate_cbf_score(user: dict, candidate: dict, selected_location: dict | None = None, candidate_prices=None) -> float:
    candidate_prices = candidate_prices or []
    item_type = normalize_entity_type(candidate.get("item_type", ""))

    if item_type == "locations":
        score = _location_score(user, candidate)
    elif item_type == "hotels":
        score = _hotel_score(user, candidate)
    elif item_type == "guides":
        score = _guide_score(user, candidate, selected_location, candidate_prices)
    elif item_type == "vehicles":
        score = _vehicle_score(user, candidate, selected_location, candidate_prices)
    elif item_type == "shop_items":
        score = _shop_item_score(user, candidate, selected_location, candidate_prices)
    else:
        score = 0.0

    return float(np.clip(score, 0.0, 1.0))