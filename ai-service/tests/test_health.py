from fastapi.testclient import TestClient

from app.main import create_app

client = TestClient(create_app())


def test_health_reports_ok() -> None:
    response = client.get("/health")

    assert response.status_code == 200
    assert response.json()["status"] == "ok"


def test_index_exposes_documented_entrypoints() -> None:
    response = client.get("/")

    assert response.status_code == 200
    assert response.json()["health"] == "/health"
