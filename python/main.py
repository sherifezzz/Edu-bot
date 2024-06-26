from flask import Flask, jsonify, request, session
from flask_cors import CORS
from flask_session import Session
import mysql.connector
from sqlalchemy import create_engine, text
from sqlalchemy.exc import SQLAlchemyError
from langchain_community.utilities import SQLDatabase
from langchain_core.prompts import ChatPromptTemplate
from langchain_core.output_parsers import StrOutputParser
from langchain_core.runnables import RunnablePassthrough
from langchain_google_genai import ChatGoogleGenerativeAI

import os

# Set Google API Key
os.environ['GOOGLE_API_KEY'] = "AIzaSyB8QFGDe0fEeP4tzZcvgcxvRaM9ZuBGtdI"

# MySQL database URI
mysql_uri = "mysql+mysqlconnector://root@localhost:3306/chatbot_login"

# Initialize SQL Database
db = SQLDatabase.from_uri(mysql_uri)

# Define the chat prompt template for SQL query generation
sql_prompt_template = """Based on the table schema below, write a SQL query that would answer the user's question:
{schema}

Question: {question}
Student ID: {student_id}
SQL Query:"""

# Create a ChatPromptTemplate for SQL queries
sql_prompt = ChatPromptTemplate.from_template(sql_prompt_template)
def get_data(id):
    conn = mysql.connector.connect(host='localhost', user='root', password='', database='chatbot_login')
    cursor = conn.cursor()
    sql = "SELECT * FROM students_courses WHERE student_id=%s"
    val = (id,)
    cursor.execute(sql, val)
    result = cursor.fetchall()
    return result
    cursor.close()
    conn.close()
    
    
    
# Function to get table schema
def get_schema(_):
    schema = db.get_table_info()
    return schema

# Google Generative AI model initialization
llm = ChatGoogleGenerativeAI(model="gemini-pro", temperature=0.7)

# Define the SQL chain
sql_chain = (
    RunnablePassthrough.assign(schema=get_schema)
    | sql_prompt
    | llm.bind(stop=["\nSQLResult:"])
    | StrOutputParser()
)

# Define the chat prompt template for natural language response
nl_prompt_template = """You are a helpful assistant. Answer the following questions considering the table schema below, and provide a natural language response:
{schema}

Question: {question}
Student ID: {student_id}
SQL Query: {query}
SQL Response: {response}"""

# Create ChatPromptTemplate for natural language response
nl_prompt = ChatPromptTemplate.from_template(nl_prompt_template)

# Function to run SQL query
def run_query(query):
    query = query.strip('```sql\n').strip('```')
    print(f"Executing SQL query: {query}")  # Added logging for the SQL query
    try:
        result = db.run(query)
        print(f"Query result: {result}")  # Added logging for the query result
        return result
    except Exception as e:
        print(f"SQL execution error: {str(e)}")
        return str(e)

# Define the full chain
full_chain = (
    RunnablePassthrough.assign(query=sql_chain).assign(
        schema=get_schema,
        response=lambda vars: run_query(vars["query"]),
        student_id=lambda vars: session.get('student_id')  # Ensure student_id is part of the chain context
    )
    | nl_prompt
    | llm
)

# Initialize Flask app
app = Flask(__name__)
CORS(app)
 # Configure CORS
app.config['SECRET_KEY'] = 'your_secret_key'
app.config['SESSION_TYPE'] = 'filesystem'
Session(app)

# Initialize SQLAlchemy engine
engine = create_engine(mysql_uri)

@app.route('/login', methods=['POST', 'OPTIONS'])
def login():
    if request.method == 'OPTIONS':
        return '', 200

    data = request.json
    username = data.get('username')
    password = data.get('password')

    try:
        with engine.connect() as connection:
            result = connection.execute(
                text("SELECT * FROM students WHERE username=:username AND password=:password"),
                {"username": username, "password": password}
            ).fetchone()

        if result:
            session['student_id'] = result['student_id']
            return jsonify({'message': 'Login successful', 'student_id': result['student_id']}), 200
        else:
            return jsonify({'message': 'Invalid credentials'}), 401
    except SQLAlchemyError as e:
        print(f"SQLAlchemy error: {str(e)}")
        return jsonify({'message': 'Database error', 'error': str(e)}), 500

@app.route('/courses', methods=['POST'])
def MAIN():
    if request.method == 'OPTIONS':
        return '', 200
    student_id = request.form["student-id"]
    try:
        result = get_data(student_id)
        courses = [{'student_id': int(row[0]), 'course_id': int(row[1])} for row in result]
        return jsonify(courses), 200
    except SQLAlchemyError as e:
        print(f"SQLAlchemy error: {str(e)}")
        return jsonify({'message': 'Database error', 'error': str(e)}), 500

@app.route('/edubot', methods=['POST', 'OPTIONS'])
def edubot():
    if request.method == 'OPTIONS':
        return '', 200

    try:
        question = request.form.get('question')
        print(question)
        student_id = request.form.get('student-id')
        if not student_id:
            return jsonify({'response': 'You need to be logged in to ask about your courses or tasks.'}), 401

        if not question:
            return jsonify({'response': 'It seems like you didn\'t ask a question. Can you please provide more details?'}), 200
        
        greetings = ["hello", "hi", "greetings", "hey", "good morning", "good afternoon", "good evening"]
        if any(greet in question.lower() for greet in greetings):
            print("Greeting detected, responding with a greeting.")
            return jsonify({'response': 'Hello! How can I assist you today?'}), 200
        
        output = full_chain.invoke({"question": question, "student_id": student_id})
        response_content = output.content

        return jsonify({'response': response_content}), 200
    except Exception as e:
        return jsonify({'error': str(e)}), 500
    
    
if __name__ == '__main__':
    app.run(debug=True,port=5005)