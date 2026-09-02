# pyrefly: ignore [missing-import]
from sqlalchemy import create_engine
from app.core.config import DATABASE_URL

# pool_pre_ping=True ensures the connection is alive before executing queries
engine = create_engine(DATABASE_URL, pool_pre_ping=True)