<h1>Authentication Backend Server with REST API</h1>
<h2>Introduction</h2>
This folder contains the backend server code for login authentication, built using <b>Python 3.11.6</b> programming language and <b>Flask</b> framework. This should be deployed as REST API.    
<h2>Getting Started</h2>
To launch the login authentication server, please install the dependencies through the commands below:  

```
python -m venv venv
venv\Scripts\activate
pip install -r requirements.txt
flask run
```
The server will be running in localhost, with the default port 5000.  
A .db file will be created automatically when the server is launched, this is the SQLite3 database. If you wish to change the filename, please do it at `config.py`. It will store user credentials and tickets.   