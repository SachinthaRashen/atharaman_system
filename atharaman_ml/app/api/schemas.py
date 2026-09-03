from typing import Any

from pydantic import BaseModel, Field


class Candidate(BaseModel):

    item_type: str

    item_id: int

    # Optional entity/context fields.
    # Laravel can send whatever it already has.

    latitude: float | None = None
    longitude: float | None = None

    budget_tier: str | None = None
    pricing_model: str | None = None
    base_price: float | None = None

    max_total_capacity: int | None = None
    is_wheelchair_accessible: bool | None = None

    vehicle_type: str | None = None
    rental_type: str | None = None
    terrain_capability: str | None = None
    rate_per_day: float | None = None
    rate_per_km: float | None = None
    passenger_capacity: int | None = None

    specialty: str | None = None
    languages_spoken: Any = None
    daily_rate: float | None = None
    experience_years: int | None = None

    location_type: str | None = None
    terrain_difficulty: str | None = None
    requires_4x4: bool | None = None
    requires_guide: bool | None = None
    elevation_meters: float | None = None

    item_category: str | None = None
    rental_price_per_day: float | None = None
    stock_quantity: int | None = None


class UserProfile(BaseModel):

    user_id: int

    age: float | None = 30

    country: str | None = None

    native_language: str = "English"

    preferred_travel_style: str = "adventure"

    preferred_budget_tier: str = "mid_range"

    prefers_guided_tours: bool = False

    requires_accessibility: bool = False


class LocationContext(BaseModel):

    latitude: float

    longitude: float

    location_id: int | None = None

    name: str | None = None

    location_type: str | None = None

    terrain_difficulty: str | None = None

    requires_4x4: bool = False

    requires_guide: bool = False

    elevation_meters: float | None = None


class RecommendationRequest(BaseModel):

    user: UserProfile

    candidates: list[Candidate]

    selected_location: LocationContext | None = None


class RecommendationResponse(BaseModel):

    recommendations: list[dict]