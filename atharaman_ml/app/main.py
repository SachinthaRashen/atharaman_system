from fastapi import FastAPI

from app.core.config import (
    PROJECT_NAME,
    VERSION
)

from app.api.endpoints import (
    router as recommendation_router
)


app = FastAPI(
    title=PROJECT_NAME,
    version=VERSION
)


app.include_router(
    recommendation_router
)


@app.get("/")
def root():

    return {
        "service": PROJECT_NAME,
        "version": VERSION,
        "status": "running"
    }


@app.get("/health")
def health():

    return {
        "status": "healthy"
    }