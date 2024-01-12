import string, random, datetime, sqlite3
from config import AUTH_DB

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
    insert_users_sql = '''
        INSERT INTO USERS (EMAIL, NAME, PASSWORD, ROLE, STATUS)
        VALUES ('admin@gel.com', 'admin', 'admin123', 'admin', 'active')
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
    
    sql="SELECT USER_ID, NAME, ROLE, STATUS FROM USERS WHERE EMAIL=? AND PASSWORD=?"

    cur = conn.cursor()
    cur.execute(sql, (email, password))

    user = cur.fetchone()

    if user:
        u_id = user[0]
        role = user[2]
        status = user[3]
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
        return not result

    if role == "student":
        insert_sql=f"INSERT INTO USERS(EMAIL, NAME, PASSWORD, ROLE, STATUS) VALUES (?, ?, ?, ?, 'active')"
        
    elif role == "educator":
        insert_sql=f"INSERT INTO USERS(EMAIL, NAME, PASSWORD, ROLE, STATUS) VALUES (?, ?, ?, ?, 'pending')"
    
    conn.execute(insert_sql, (email, name, password, role))

    conn.commit()

    conn.close()

    return not result

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