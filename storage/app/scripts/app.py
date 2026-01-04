import cv2
import numpy as np
import json
import os
import mysql.connector
import qrcode
import uuid
from flask import Flask, request, jsonify
from flask_cors import CORS
from insightface.app import FaceAnalysis
from scipy.spatial.distance import cosine

app = Flask(__name__)
CORS(app)

# --- 1. CONFIGURATION ---
db_config = {
    'host': '127.0.0.1',
    'user': 'root',
    'password': '',
    'database': 'ricon'
}

# --- 2. INITIALIZE MODELS ---
# Optimized for your RTX 3050
app_model = FaceAnalysis(name='buffalo_l', providers=['CUDAExecutionProvider', 'CPUExecutionProvider'])
app_model.prepare(ctx_id=0, det_size=(640, 640))
qr_detector = cv2.QRCodeDetector()

def get_db_connection():
    return mysql.connector.connect(**db_config)

# Use relative paths
QR_STORAGE_PATH = os.path.join(os.getcwd(), "public", "qrcodes")
if not os.path.exists(QR_STORAGE_PATH):
    os.makedirs(QR_STORAGE_PATH)

# --- 3. SILENCE & HEALTH ROUTES ---

@app.route('/')
def health_check():
    """Prevents 404 when hitting the base URL"""
    return jsonify({
        "status": "online",
        "service": "Ricon Model API",
        "device": "Asus Vivobook RTX 3050"
    }), 200

@app.route('/favicon.ico')
def favicon():
    """Silences the automatic browser icon request"""
    return '', 204

# --- 4. FUNCTIONAL ROUTES ---

@app.route('/generate-qr', methods=['POST'])
def generate_qr():
    data = request.json
    if not data:
        return jsonify({"error": "No JSON data provided"}), 400

    ls_id = str(data.get('locker_session_id'))
    item_detail = str(data.get('item_detail') or "barang").replace(" ", "_")
    key_data = data.get('key')

    project_root = os.path.abspath(os.path.join(os.path.dirname(__file__), "../../../"))
    folder_name = f"locker_{ls_id}"
    target_folder = os.path.join(project_root, "public", "images", "qr", folder_name)

    os.makedirs(target_folder, exist_ok=True)

    file_name = f"qr_{item_detail}.png"
    file_path = os.path.join(target_folder, file_name)

    img = qrcode.make(key_data)
    img.save(file_path)

    return jsonify({
        "status": "success",
        "relative_path": f"images/qr/{folder_name}/{file_name}"
    })

@app.route('/recognize', methods=['POST'])
def recognize():
    if 'images' not in request.files:
        return jsonify({"error": "No images uploaded"}), 400

    file = request.files['images']
    img = cv2.imdecode(np.frombuffer(file.read(), np.uint8), cv2.IMREAD_COLOR)

    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)

    try:
        # STEP A: ONLY DECODE QR
        qr_data, points, _ = qr_detector.detectAndDecode(img)
        if qr_data:
            return jsonify([{"type": "qr_raw", "key": qr_data}])

        # STEP B: FACE RECOGNITION FALLBACK
        best_name, best_id, min_dist = "STRANGER", None, 0.45
        faces = app_model.get(img)

        if faces:
            target_emb = faces[0].normed_embedding
            cursor.execute("SELECT id, name, face_embedding FROM users WHERE face_embedding IS NOT NULL")
            for record in cursor.fetchall():
                db_emb = np.array(json.loads(record['face_embedding']))
                dist = cosine(target_emb, db_emb)
                if dist < min_dist:
                    min_dist, best_name, best_id = dist, record['name'], record['id']

        return jsonify([{"type": "face", "result": best_name, "user_id": best_id}])

    finally:
        cursor.close()
        conn.close()

if __name__ == '__main__':
    # host='0.0.0.0' allows your S25 Ultra to connect via local IP
    app.run(host='0.0.0.0', port=5000, debug=False)


