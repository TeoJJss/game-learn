from flask import Blueprint, request, jsonify
from services import *

blueprint = Blueprint('blueprint', __name__)

@blueprint.route("/")
def index():
    return jsonify({"msg": "nothing here"}), 200

@blueprint.route("/login", methods=["POST"])
def login():
    body = request.json
    email = body.get("email")
    password = body.get("password")

    u_id, ticket, role, status = generate_ticket(email, password)
    if status != "active":
        if status == "pending":
            return jsonify({"msg": "Educator's registration is waiting for approval", "role": role}), 401
        elif status == "banned":
            return jsonify({"msg": "The user is banned", "role": role}), 401
    if not ticket: 
        return jsonify({"msg": "Unauthenticated"}), 400
    return jsonify({"ticket": ticket}), 201

@blueprint.route("/register", methods=["POST"])
def register():
    body = request.json
    email = body.get("email")
    name = body.get("name")
    password = body.get("password")
    role = body.get("role")

    registration_status = register_user(email, name, password, role)
    if not registration_status:
        return jsonify({"msg": "User already exists"}), 403
    return jsonify({"msg": "Registered"}), 200

@blueprint.route("/check-ticket", methods=["GET"])
def check():
    ticket = request.args.get("ticket")

    count, result = check_ticket(ticket)

    if count:
        return jsonify({"msg": "Allowed", "data": result}), 202
    else:
        return jsonify({"msg": "Not Allowed", "data":[]}), 401

@blueprint.route("/logout", methods=["DELETE"])
def logout():
    body = request.json
    tic = body.get("ticket")

    rm_ticket(tic)
    return jsonify({"msg": "Logout success"}), 200

@blueprint.route("/update_prof", methods=["PATCH"])
def update_profile():
    body = request.json
    tic = body.get("ticket")
    email = body.get("email")
    name = body.get("name")

    msg, code = update_usr_prof(tic, email, name)
    return jsonify({"msg": msg}), code

@blueprint.route("/update_pass", methods=["PATCH"])
def update_password():
    body = request.json
    tic = body.get("ticket")
    email = body.get("email")
    name = body.get("name")
    new_pass = body.get("new_pass")
    
    msg, code = reset_pass(tic, email, name, new_pass)
    return jsonify({"msg": msg}), code