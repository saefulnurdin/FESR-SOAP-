import importlib.util

from fastapi import APIRouter

from app.config import get_settings
from app.schemas.health import HealthResponse

router = APIRouter(tags=["health"])


def whisper_available() -> bool:
    """Whether faster-whisper is installed in the current environment."""
    return importlib.util.find_spec("faster_whisper") is not None


@router.get("/health", response_model=HealthResponse)
def health() -> HealthResponse:
    settings = get_settings()

    return HealthResponse(
        status="ok",
        version=settings.app_version,
        whisper_available=whisper_available(),
        whisper_model=settings.whisper_model,
    )
