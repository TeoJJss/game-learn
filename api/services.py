import string, random, datetime, sqlite3
from config import AUTH_DB
from werkzeug.security import generate_password_hash, check_password_hash

auth_db=AUTH_DB

def create_db():
    # Create users table
    conn = sqlite3.connect(auth_db)
    sql='''
        CREATE TABLE USERS
        (
            USER_ID INTEGER PRIMARY KEY AUTOINCREMENT,
            EMAIL VARCHAR(50) NOT NULL,
            NAME VARCHAR(50) NOT NULL,
            PASSWORD VARCHAR(255) NOT NULL,
            ROLE VARCHAR(50) NOT NULL,
            STATUS VARCHAR(50) NOT NULL,
            REMARK VARCHAR(100) NULL
        )
        '''
    conn.execute(sql)

    # Add admin
    admin_pass = str(generate_password_hash("admin123"))
    insert_users_sql = f'''
        INSERT INTO USERS (EMAIL, NAME, PASSWORD, ROLE, STATUS)
        VALUES ('admin@gel.com', 'admin', '{admin_pass}', 'admin', 'active')
    '''
    conn.execute(insert_users_sql)

    conn.commit()
    conn.close()

    # Create tickets table
    conn = sqlite3.connect(auth_db)

    tic_sql='''
        CREATE TABLE TICKETS
        (
            TICKET VARCHAR(100) PRIMARY KEY NOT NULL,
            USER_ID INTEGER NOT NULL,
            FOREIGN KEY (USER_ID) REFERENCES USERS(USER_ID)
        )
        '''
    conn.execute(tic_sql)
    conn.commit()
    conn.close()

def generate_ticket(email, password):

    # Check if credentials are valid
    conn = sqlite3.connect(auth_db)
    
    sql="SELECT PASSWORD, USER_ID, NAME, ROLE, STATUS FROM USERS WHERE EMAIL=?"

    cur = conn.cursor()
    cur.execute(sql, (email,))

    user = cur.fetchone()

    if user and check_password_hash(user[0], password):
        u_id = user[1]
        role = user[3]
        status = user[4]
        if status != "active":
            return u_id, 0, role, status
    
    else:
        return 0, False, 0, 0

    # Generate ticket
    letters = string.ascii_lowercase + string.ascii_uppercase
    result_str = ''.join(random.choice(letters) for i in range(26))

    dt= datetime.datetime.now().strftime("%f")
    ticket=f"GEL-{result_str}-{dt}"
    insert_ticket(ticket, u_id, role)

    return u_id, ticket, role, status

def insert_ticket(ticket, u_id, role):
    conn = sqlite3.connect(auth_db)

    dlt_sql = f"DELETE FROM TICKETS WHERE USER_ID=?"
    conn.execute(dlt_sql, (u_id,))
    conn.commit()

    insert_sql=f"INSERT INTO TICKETS (TICKET, USER_ID) VALUES (?, ?)"

    conn.execute(insert_sql, (ticket, u_id))
    conn.commit()

    conn.close()

def register_user(email, name, password, role):
    conn = sqlite3.connect(auth_db)

    sql=f"SELECT COUNT(*) FROM USERS WHERE EMAIL=?"
    result = conn.execute(sql, (email,)).fetchone()[0]

    if result > 0:
        return not result, 0

    if role == "student":
        insert_sql=f"INSERT INTO USERS(EMAIL, NAME, PASSWORD, ROLE, STATUS) VALUES (?, ?, ?, ?, 'active')"
        
    elif role == "educator":
        insert_sql=f"INSERT INTO USERS(EMAIL, NAME, PASSWORD, ROLE, STATUS) VALUES (?, ?, ?, ?, 'pending')"
    
    conn.execute(insert_sql, (email, name, generate_password_hash(password), role))

    conn.commit()

    sql=f"SELECT USER_ID FROM USERS WHERE EMAIL=?"
    user_id = conn.execute(sql, (email,)).fetchone()[0]

    conn.close()

    return not result, user_id

def check_ticket(ticket):
    conn = sqlite3.connect(auth_db)

    sql = "SELECT COUNT(*), USER_ID FROM TICKETS WHERE TICKET=?"
    count, user_id = conn.execute(sql, (ticket,)).fetchone()
    result = None
    if count:
        sql =  "SELECT EMAIL, NAME, ROLE, STATUS FROM USERS WHERE USER_ID=?"
        result = conn.execute(sql, (user_id,)).fetchall()[0]

        email = result[0]
        name = result[1]
        role = result[2]
        status = result[3]
        result = {
            "user_id": user_id,
            "email": email,
            "name": name,
            "role": role,
            "status": status
        }
    conn.close()
    return count, result

def rm_ticket(ticket):
    conn = sqlite3.connect(auth_db)
    dlt_sql = "DELETE FROM TICKETS WHERE TICKET=?"
    conn.execute(dlt_sql, (ticket,))
    conn.commit()

    conn.close()

def update_usr_prof(ticket, email, name):
    conn = sqlite3.connect(auth_db)
    get_usr_sql = "SELECT USER_ID FROM TICKETS WHERE TICKET=?"
    user_id = conn.execute(get_usr_sql, (ticket,)).fetchone()[0]

    if user_id:
        chk_em_sql = "SELECT COUNT(*) FROM USERS WHERE USER_ID!=? AND EMAIL=?"
        c_em = conn.execute(chk_em_sql, (user_id, email)).fetchone()[0]
        if c_em:
            return "The email has been registered!", 400
        update_sql = "UPDATE USERS SET EMAIL=?, NAME=? WHERE USER_ID=?"
        conn.execute(update_sql, (email, name, user_id))
        conn.commit()
        conn.close()
        
        return "Update success", 200
    conn.close()
    return "Invalid ticket", 400

def reset_pass(ticket, email, name, new_pass):
    conn = sqlite3.connect(auth_db)

    get_usr_sql = "SELECT USER_ID FROM TICKETS WHERE TICKET=?"
    user_id = conn.execute(get_usr_sql, (ticket,)).fetchone()[0]
    
    if user_id:
        chk_usr_sql = "SELECT COUNT(*) FROM USERS WHERE USER_ID=? AND EMAIL=? AND NAME=?"
        c_usr = conn.execute(chk_usr_sql, (user_id, email, name)).fetchone()[0]
        if not c_usr:
            return "Incorrect details entered!", 400
        
        update_sql = "UPDATE USERS SET PASSWORD=? WHERE USER_ID=?"
        conn.execute(update_sql, (new_pass, user_id))
        conn.commit()
        conn.close()

        return "Update password success", 200
    
    conn.close()
    return "Invalid ticket", 400

def update_status(ticket, user_id, new_status, remark=None):
    conn = sqlite3.connect(auth_db)
    if new_status not in ["active", "banned"]:
        return "Invalid new status", 400
    validate_sql = "SELECT ROLE FROM USERS LEFT JOIN TICKETS ON USERS.USER_ID = TICKETS.USER_ID WHERE TICKET=?"
    c_usr_role = conn.execute(validate_sql, (ticket,)).fetchone()[0]
    if c_usr_role != "admin":
        return "Unauthorized access", 401
    update_sql = "UPDATE USERS SET STATUS = ?, REMARK = ? WHERE USER_ID = ?"
    conn.execute(update_sql, (new_status, remark, user_id))
    conn.commit()
    conn.close()

    return "Update status success", 200

def get_edu_list(ticket):
    conn = sqlite3.connect(auth_db)
    validate_sql = "SELECT ROLE FROM USERS LEFT JOIN TICKETS ON USERS.USER_ID = TICKETS.USER_ID WHERE TICKET=?"
    c_usr_role = conn.execute(validate_sql, (ticket,)).fetchone()[0]
    if c_usr_role != "admin":
        return "Unauthorized access", 401
    ls_sql = "SELECT EMAIL, NAME, ROLE, STATUS, REMARK, USER_ID FROM USERS WHERE ROLE='educator'"
    usr_ls = conn.execute(ls_sql).fetchall()

    edu_ls = []
    print(usr_ls)
    for usr in usr_ls:
        tmp_d = {
            "email": usr[0],
            "name": usr[1],
            "role": usr[2],
            "status": usr[3],
            "remark": usr[4],
            "user_id": usr[5]
        }
        edu_ls.append(tmp_d)
    conn.close()
    return edu_ls, 200