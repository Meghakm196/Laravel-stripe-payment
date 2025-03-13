<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stripe Multi Payment</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #f7f7f7;
            text-align: center;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
        }

        p {
            color: #666;
            font-size: 16px;
            margin-bottom: 20px;
        }

        .amount-input {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            text-align: center;
        }

        .buttons {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .btn {
            display: block;
            text-decoration: none;
            font-size: 18px;
            padding: 12px;
            border-radius: 5px;
            color: white;
            transition: 0.3s ease;
            text-align: center;
        }

        .btn:hover {
            transform: scale(1.05);
        }

        /* Button Colors */
        .card { background-color: #007bff; } /* Card */
        .gpay { background-color: #4285F4; } /* Google Pay */
        .afterpay { background-color: #ff4081; } /* Afterpay */
        .alipay { background-color: #ffcc00; color: black; } /* Alipay */

        /* Loading Effect */
        .loading {
            display: none;
            margin-top: 20px;
            padding: 10px;
            color: #fff;
            background: #17a2b8;
            border-radius: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Choose Your Payment Method</h1>
        <p>Enter the amount you want to pay and select a payment option:</p>

        <input type="number" id="amount" class="amount-input" placeholder="Enter amount in AUD" min="1" required>

        <div class="buttons">
            <a href="#" class="btn card" onclick="redirectToCheckout('card')">💳 Pay with Card</a>
            <a href="#" class="btn afterpay" onclick="redirectToCheckout('afterpay')">💰 Afterpay</a>
            <a href="#" class="btn alipay" onclick="redirectToCheckout('alipay')">🌏 Alipay</a>
        </div>

        <div id="loading" class="loading">
            <p>Processing your payment... Please wait.</p>
        </div>
    </div>

    <script>
        function redirectToCheckout(method) {
            var amount = document.getElementById("amount").value;

            if (!amount || amount <= 0) {
                alert("Please enter a valid amount.");
                return;
            }

            $("#loading").fadeIn();
            window.location.href = "/checkout/" + method + "?amount=" + amount;
        }
    </script>
</body>
</html>
