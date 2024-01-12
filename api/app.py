from flask import Flask
from blueprint import blueprint
import os
from config import AUTH_DB
from services import create_db

app = Flask(__name__)
app.register_blueprint(blueprint, url_prefix='/login-api')

auth_db=AUTH_DB

if not os.path.isfile(auth_db):
    create_db()