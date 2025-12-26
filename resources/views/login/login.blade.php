<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>myBox – Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #f2f2f2; /* background abu-abu */
        }

        /* ==== WRAPPER ==== */
        .layout {
            display: flex;
            width: 100%;
            max-width: 1100px;
            margin: 40px auto;
            transition: all 0.3s ease;
        }

        /* ==== LEFT IMAGE ==== */
        .left {
            width: 48%;
            background-image: url('{{ asset('images/locker.png') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        /* ==== RIGHT FORM ==== */
        .right {
            width: 52%;
            padding: 60px 70px 40px 70px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            transition: all 0.3s ease;
        }

        .logo {
            width: 150px;
            margin: 0 auto 10px auto;
            display: block;
        }

        h1 {
            text-align: center;
            font-size: 42px;
            color: #0d2951;
            font-weight: 600;
            margin-bottom: 25px;
        }

        label {
            font-size: 15px;
            font-weight: 500;
            margin-bottom: 5px;
            display: inline-block;
        }

        input {
            width: 100%;
            padding: 14px;
            border-radius: 10px;
            border: 1.8px solid #1e7898;
            outline: none;
            font-size: 15px;
            margin-bottom: 6px;
            background: #eaf3ff;
        }

        input::placeholder {
            color: #96a2af;
            font-style: italic;
        }

        .error-text {
            height: 16px;
            font-size: 12px;
            color: red;
            margin-bottom: 10px;
        }

        .button {
            width: 100%;
            padding: 16px;
            margin-top: 15px;
            background: linear-gradient(to right, #33d6a6, #1e3989);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }

        .button:hover {
            opacity: 0.85;
        }

        /* ==== RESPONSIVE ==== */
        @media (max-width: 900px) {
            .layout {
                flex-direction: column;
                max-width: 500px;
                margin: 20px auto;
            }

            /* hilangkan locker image di mobile */
            .left {
                display: none;
            }

            .right {
                width: 100%;
                padding: 30px 25px 40px 25px;
            }
        }
    </style>
</head>

<body>
    <div class="layout">

        <!-- LEFT IMAGE -->
        <div class="left"></div>

        <!-- RIGHT FORM -->
        <div class="right">
            <img src="{{ asset('images/logo.png') }}" class="logo">
            <h1>myBox</h1>

            <label>udomain:</label>
            <input id="udomain" placeholder="enter your udomain (ex: u123456)">
            <div id="udomainError" class="error-text"></div>

            <label>password:</label>
            <input id="password" type="password" placeholder="enter your password">
            <div id="passwordError" class="error-text"></div>

            <button class="button" onclick="login()">Log in</button>
        </div>

    </div>

    <script>
        async function login() {
            const udomainError = document.getElementById('udomainError');
            const passwordError = document.getElementById('passwordError');

            udomainError.innerText = "";
            passwordError.innerText = "";

            const udomain = document.getElementById('udomain').value;
            const password = document.getElementById('password').value;

            const res = await fetch("{{ url('/api/login') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                },
                body: JSON.stringify({ udomain, password })
            });

            const data = await res.json();

            if (res.status === 422) {
                if (data.errors.udomain) udomainError.innerText = data.errors.udomain[0];
                if (data.errors.password) passwordError.innerText = data.errors.password[0];
                return;
            }

            if (res.status === 401) {
                if (data.error_type === "udomain") {
                    udomainError.innerText = "Udomain tidak ditemukan.";
                } else if (data.error_type === "password") {
                    passwordError.innerText = "Password salah.";
                }
                return;
            }

            if (res.status === 200) {
                localStorage.setItem("token", data.access_token);
                window.location.href = "/";
            }
        }
    </script>
</body>

</html>
