"""Konfigurasi AI Service, dimuat dari environment atau berkas .env."""

from functools import lru_cache
from pathlib import Path

from pydantic_settings import BaseSettings, SettingsConfigDict

SERVICE_ROOT = Path(__file__).resolve().parent.parent


class Settings(BaseSettings):
    model_config = SettingsConfigDict(
        env_file=SERVICE_ROOT / ".env",
        env_file_encoding="utf-8",
        env_prefix="FESR_AI_",
        extra="ignore",
    )

    app_name: str = "FESR AI Service"
    app_version: str = "0.1.0"
    debug: bool = False

    host: str = "127.0.0.1"
    port: int = 8001

    cors_origins: list[str] = [
        "http://localhost:8000",
        "http://127.0.0.1:8000",
    ]

    whisper_model: str = "small"
    whisper_device: str = "cpu"
    whisper_compute_type: str = "int8"
    whisper_language: str = "id"
    whisper_model_dir: str = "models"


@lru_cache
def get_settings() -> Settings:
    return Settings()
