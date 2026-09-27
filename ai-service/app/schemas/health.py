from pydantic import BaseModel


class HealthResponse(BaseModel):
    status: str
    version: str
    whisper_available: bool
    whisper_model: str
