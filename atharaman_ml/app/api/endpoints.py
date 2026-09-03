from fastapi import APIRouter, HTTPException

from app.api.schemas import (
    RecommendationRequest,
    RecommendationResponse
)

from app.data.loader import (
    load_interaction_matrix
)

from app.ml.inference import (
    rank_live_candidates
)


router = APIRouter(
    prefix="/recommendations",
    tags=["Recommendations"]
)


@router.post(
    "/rank",
    response_model=RecommendationResponse
)
def rank_recommendations(
    request: RecommendationRequest
):

    try:

        interactions_df = (
            load_interaction_matrix()
        )

        user = request.user.model_dump()

        candidates = [
            candidate.model_dump()
            for candidate
            in request.candidates
        ]

        selected_location = None

        if request.selected_location:

            selected_location = (
                request.selected_location
                .model_dump()
            )

        ranked = rank_live_candidates(
            user=user,
            candidates=candidates,
            interactions_df=interactions_df,
            selected_location=selected_location
        )

        return {
            "recommendations": ranked
        }

    except FileNotFoundError as exc:

        raise HTTPException(
            status_code=503,
            detail=str(exc)
        )

    except Exception as exc:

        raise HTTPException(
            status_code=500,
            detail=str(exc)
        )