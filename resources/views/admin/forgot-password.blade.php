<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Mot de passe réinitialisé - Administration</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #E8513E 0%, #c43d2b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            position: relative;
            overflow-x: hidden;
        }

        /* Animation des bulles */
        .bubbles {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            overflow: hidden;
            z-index: 0;
            pointer-events: none;
        }

        .bubble {
            position: absolute;
            bottom: -100px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: rise 20s infinite ease-in;
        }

        @keyframes rise {
            0% {
                bottom: -100px;
                transform: translateX(0) scale(0.3);
                opacity: 0;
            }
            20% {
                opacity: 0.6;
            }
            80% {
                opacity: 0.4;
            }
            100% {
                bottom: 100vh;
                transform: translateX(-50px) scale(1);
                opacity: 0;
            }
        }

        /* Container principal */
        .container {
            width: 100%;
            max-width: 500px;
            position: relative;
            z-index: 10;
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Carte de succès */
        .card {
            background: white;
            border-radius: 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            text-align: center;
        }

        /* En-tête */
        .header {
            background: linear-gradient(135deg, #E8513E 0%, #d94430 100%);
            padding: 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 4s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }

        .logo {
            width: 70px;
            height: 70px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            backdrop-filter: blur(10px);
            position: relative;
            z-index: 1;
        }

        .logo svg {
            width: 40px;
            height: 40px;
            color: white;
        }

        .header h1 {
            color: white;
            font-size: 1.75rem;
            font-weight: 700;
            position: relative;
            z-index: 1;
        }

        /* Corps */
        .body {
            padding: 2rem;
        }

        /* Animation checkmark */
        .checkmark-wrapper {
            margin: 0 auto 1.5rem;
        }

        .checkmark {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #22c55e;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            animation: scale 0.3s ease-in-out 0.4s both;
        }

        @keyframes scale {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .checkmark svg {
            width: 45px;
            height: 45px;
            color: white;
        }

        .body h2 {
            color: #1f2937;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
        }

        .body p {
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 0.5rem;
        }

        /* Bouton */
        .btn {
            display: inline-block;
            margin-top: 1.5rem;
            padding: 0.875rem 1.5rem;
            background: linear-gradient(135deg, #E8513E 0%, #d94430 100%);
            color: white;
            border: none;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(232, 81, 62, 0.4);
        }

        /* Responsive */
        @media (max-width: 640px) {
            .container {
                max-width: 100%;
            }

            .body {
                padding: 1.5rem;
            }

            .header {
                padding: 1.5rem;
            }

            .header h1 {
                font-size: 1.5rem;
            }

            .logo {
                width: 60px;
                height: 60px;
            }

            .logo svg {
                width: 32px;
                height: 32px;
            }

            .body h2 {
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body>

<div class="bubbles" id="bubbles"></div>

<div class="container">
    <div class="card">
        <div class="header">
            <div class="logo">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            <h1>Mot de passe réinitialisé !</h1>
        </div>

        <div class="body">
            <div class="checkmark-wrapper">
                <div class="checkmark">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>

            <h2>Réinitialisation réussie</h2>
            <p>Votre mot de passe a été modifié avec succès.</p>
            <p>Vous pouvez maintenant vous connecter avec votre nouveau mot de passe.</p>
        </div>
    </div>
</div>

<script>
    // Génération des bulles
    function createBubbles() {
        const bubblesContainer = document.getElementById('bubbles');
        const bubbleCount = 15;
        
        for (let i = 0; i < bubbleCount; i++) {
            const bubble = document.createElement('div');
            bubble.classList.add('bubble');
            
            const size = Math.random() * 100 + 30;
            bubble.style.width = size + 'px';
            bubble.style.height = size + 'px';
            bubble.style.left = Math.random() * 100 + '%';
            bubble.style.animationDuration = Math.random() * 20 + 10 + 's';
            bubble.style.animationDelay = Math.random() * 15 + 's';
            
            bubblesContainer.appendChild(bubble);
        }
    }
    
    createBubbles();
</script>

</body>
</html>