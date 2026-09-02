import os
from dotenv import load_dotenv

# Load variables from the .env file into the environment
load_dotenv()

# Extract variables with safe fallbacks
PROJECT_NAME = os.getenv("PROJECT_NAME", "Atharaman ML Recommendation Engine")
VERSION = os.getenv("VERSION", "1.0.0")

DATABASE_URL = os.getenv(
    "DATABASE_URL",
    "postgresql://postgres:1234@localhost:5432/atharaman_db"
)