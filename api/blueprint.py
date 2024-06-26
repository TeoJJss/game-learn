from flask import Blueprint, request, jsonify
from services import *

blueprint = Blueprint('blueprint', __name__)

@blueprint.route("/") #http://127.0.0.1:5000/login-api/
def index():
    return jsonify({"msg": "nothing here"}), 200

@blueprint.route("/login", methods=["POST"]) #http://127.0.0.1:5000/login-api/login
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

@blueprint.route("/register", methods=["POST"]) #http://127.0.0.1:5000/login-api/register
def register():
    body = request.json
    email = body.get("email")
    name = body.get("name")
    password = body.get("password")
    role = body.get("role")

    registration_status, user_id = register_user(email, name, password, role)
    if not registration_status:
        return jsonify({"msg": "User already exists", "user_id": 0}), 403
    return jsonify({"msg": "Registered", "user_id": str(user_id)}), 200

@blueprint.route("/check-ticket", methods=["GET"]) #http://127.0.0.1:5000/login-api/check-ticket?ticket=your_ticket_value
def check():
    ticket = request.args.get("ticket")

    count, result = check_ticket(ticket)

    if count:
        return jsonify({"msg": "Allowed", "data": result}), 202
    else:
        return jsonify({"msg": "Not Allowed", "data":[]}), 401

@blueprint.route("/logout", methods=["DELETE"]) #http://127.0.0.1:5000/login-api/logout
def logout():
    body = request.json
    tic = body.get("ticket")

    rm_ticket(tic)
    return jsonify({"msg": "Logout success"}), 200

@blueprint.route("/update-prof", methods=["PATCH"]) #http://127.0.0.1:5000/login-api/update-prof
def update_profile():
    body = request.json
    tic = body.get("ticket")
    email = body.get("email")
    name = body.get("name")

    msg, code = update_usr_prof(tic, email, name)
    return jsonify({"msg": msg}), code

@blueprint.route("/update-pass", methods=["PATCH"]) #http://127.0.0.1:5000/login-api/update-pass
def update_password():
    body = request.json
    email = body.get("email")
    name = body.get("name")
    new_pass = body.get("password")
    
    msg, code = reset_pass(email, name, new_pass)
    return jsonify({"msg": msg}), code

@blueprint.route("/update-status", methods=["PATCH"]) #http://127.0.0.1:5000/login-api/update-status
def update_usr_status():
    body = request.json
    tic = body.get("ticket")
    user_id = body.get("user_id")
    new_status = body.get("new_status")
    remark = body.get("remark") if body.get("remark") !=  'null' else ""
    msg, code = update_status(tic, user_id, new_status, remark)

    return jsonify({"msg": msg}), code

@blueprint.route("/edu-list", methods=["GET"]) #http://127.0.0.1:5000/login-api/edu-list?ticket=your_ticket_value
def get_edu_ls():
    tic = request.args.get("ticket")

    msg, code = get_edu_list(tic)
    return jsonify({"msg": msg}), code

@blueprint.route('/user-list', methods=['GET']) #http://127.0.0.1:5000/login-api/user-list?ticket=your_ticket_value
def get_user_ls():
    tic = request.args.get("ticket")

    msg, code = get_user_list(tic) 
    return jsonify({"msg": msg}), code

@blueprint.route("/user-detail", methods=["GET"]) #http://127.0.0.1:5000/login-api/user-detail?user_id=user_id_value
def get_usr_details():
    u_id = request.args.get("user_id")
    username, code = get_usr(u_id)
    return jsonify({"msg": username}), code