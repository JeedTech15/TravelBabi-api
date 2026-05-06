<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code OTP - Travel Babi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .otp-card {
            max-width: 520px;
            width: 100%;
            margin: auto;
            background: #ffffff;
            border-radius: 32px;
            box-shadow: 0 25px 45px -12px rgba(0, 0, 0, 0.35), 0 8px 18px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.2s ease;
        }

        .otp-card:hover {
            transform: translateY(-3px);
        }

        /* Header orange */
        .otp-header {
            background: #FF6B00;
            padding: 32px 24px 28px;
            text-align: center;
            position: relative;
        }

        .otp-header::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 0;
            right: 0;
            height: 20px;
            background: #ffffff;
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
        }

        .shield-icon {
            background: rgba(255, 255, 255, 0.2);
            width: 70px;
            height: 70px;
            line-height: 70px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            backdrop-filter: blur(2px);
        }

        .shield-icon svg {
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }

        .otp-header h2 {
            color: #ffffff;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.3px;
            margin: 0 0 6px 0;
        }

        .otp-header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 15px;
            font-weight: 500;
            margin: 0;
        }

        /* Contenu blanc */
        .otp-body {
            padding: 40px 32px 36px;
            background: #ffffff;
            text-align: center;
        }

        .security-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #FFF3E8;
            padding: 8px 18px;
            border-radius: 60px;
            margin-bottom: 28px;
            font-size: 13px;
            font-weight: 600;
            color: #FF6B00;
        }

        .security-badge span {
            font-size: 14px;
        }

        .message {
            color: #1e1e1e;
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 28px;
            line-height: 1.5;
        }

        .code-container {
            background: #F9F9F9;
            border-radius: 24px;
            padding: 16px 12px;
            margin: 20px 0 24px;
            border: 1px solid #EEEEEE;
            transition: all 0.2s;
        }

        .otp-code {
            font-size: 58px;
            font-weight: 800;
            letter-spacing: 12px;
            color: #000000;
            background: #ffffff;
            display: inline-block;
            padding: 12px 20px;
            border-radius: 20px;
            font-family: 'Courier New', 'SF Mono', monospace;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03), inset 0 1px 0 rgba(0,0,0,0.02);
            border: 1px solid #E8E8E8;
        }

        .digit-highlight {
            background: linear-gradient(120deg, #FF6B0010 0%, #FF6B0010 40%, transparent 60%);
            padding: 0 4px;
            border-radius: 8px;
        }

        .info-note {
            background: #FEF7F0;
            border-left: 4px solid #FF6B00;
            padding: 16px 20px;
            border-radius: 16px;
            margin: 28px 0 20px;
            text-align: left;
        }

        .info-note p {
            color: #2c2c2c;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
        }

        .info-note small {
            color: #FF6B00;
            font-weight: 600;
        }

        .timer-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #1e1e1e;
            color: white;
            padding: 6px 14px;
            border-radius: 40px;
            font-size: 12px;
            font-weight: 500;
            margin-top: 10px;
        }

        hr {
            border: none;
            height: 1px;
            background: linear-gradient(to right, #e0e0e0, #FF6B00, #e0e0e0);
            margin: 32px 0 20px;
        }

        .footer {
            text-align: center;
            padding: 0 32px 36px;
            background: white;
        }

        .footer small {
            color: #999;
            font-size: 12px;
            letter-spacing: 0.3px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .brand {
            font-weight: 700;
            color: #FF6B00;
        }

        /* Animation subtile */
        @keyframes gentlePulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.02); opacity: 0.95; }
            100% { transform: scale(1); opacity: 1; }
        }

        .otp-code {
            animation: gentlePulse 0.8s ease-in-out;
        }

        /* Responsive */
        @media (max-width: 560px) {
            .otp-body {
                padding: 32px 20px;
            }
            .otp-code {
                font-size: 42px;
                letter-spacing: 8px;
                padding: 8px 16px;
            }
            .otp-header h2 {
                font-size: 24px;
            }
        }

        /* Pour les lecteurs d'écran */
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
        }
    </style>
</head>
<body>
    <div class="otp-card">
        <div class="otp-header">
            <div class="shield-icon">
                <svg width="34" height="34" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2L3 6V12C3 16.97 7.03 21 12 21C16.97 21 21 16.97 21 12V6L12 2Z" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                    <path d="M12 11V16M12 7H12.01" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h2>Vérification · OTP</h2>
            <p>Code unique à usage sécurisé</p>
        </div>

        <div class="otp-body">

            <div class="message">
                Bonjour,<br>
                <strong style="color:#FF6B00;">voici votre code de sécurité</strong> à transmettre à l’administrateur.
            </div>

            <div class="code-container">
                <div class="otp-code" aria-label="Code de sécurité à 6 chiffres">
                    {{ $digit }}
                </div>
                <div class="timer-badge">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10" stroke="white" stroke-width="1.5"/>
                        <polyline points="12 6 12 12 16 14" stroke="white" stroke-width="1.5" fill="none"/>
                    </svg>
                    <span>Valable pour cette session</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>