<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Réinitialisation du mot de passe - Administration</title>
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
            max-width: 550px;
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

        /* Carte du formulaire */
        .card {
            background: white;
            border-radius: 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.3);
        }

        /* En-tête avec logo */
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
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }

        .header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.875rem;
            position: relative;
            z-index: 1;
        }

        /* Corps du formulaire */
        .body {
            padding: 2rem;
        }

        /* Barre de progression */
        .password-strength {
            margin-top: 0.5rem;
            margin-bottom: 1rem;
        }

        .strength-bar {
            height: 4px;
            background: #e5e7eb;
            border-radius: 2px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .strength-fill {
            height: 100%;
            width: 0;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-text {
            font-size: 0.7rem;
            margin-top: 0.25rem;
            color: #6b7280;
        }

        /* Message de statut */
        .status-message {
            margin-bottom: 1.5rem;
            padding: 1rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideDown 0.4s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .status-success {
            background-color: #f0fdf4;
            border-left: 4px solid #22c55e;
            color: #166534;
        }

        .status-error {
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
        }

        .status-icon {
            flex-shrink: 0;
        }

        /* Groupe de formulaire */
        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            color: #9ca3af;
            pointer-events: none;
        }

        .toggle-password {
            position: absolute;
            right: 1rem;
            background: none;
            border: none;
            cursor: pointer;
            color: #9ca3af;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            width: auto;
            transition: color 0.3s ease;
        }

        .toggle-password:hover {
            color: #E8513E;
            transform: none;
            box-shadow: none;
        }

        .toggle-password::before {
            display: none;
        }

        input {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 3rem;
            font-size: 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 1rem;
            outline: none;
            transition: all 0.3s ease;
            font-family: inherit;
            background-color: #f9fafb;
        }

        input:focus {
            border-color: #E8513E;
            background-color: white;
            box-shadow: 0 0 0 4px rgba(232, 81, 62, 0.1);
        }

        input:hover:not(:focus) {
            border-color: #d1d5db;
            background-color: white;
        }

        input.input-error {
            border-color: #ef4444;
        }

        .error-message {
            font-size: 0.75rem;
            color: #ef4444;
            margin-top: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        /* Bouton */
        button[type="submit"] {
            width: 100%;
            padding: 0.875rem;
            background: linear-gradient(135deg, #E8513E 0%, #d94430 100%);
            color: white;
            border: none;
            border-radius: 1rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            position: relative;
            overflow: hidden;
        }

        button[type="submit"]::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        button[type="submit"]:hover::before {
            width: 300px;
            height: 300px;
        }

        button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(232, 81, 62, 0.4);
        }

        button[type="submit"]:active {
            transform: translateY(0);
        }

        button[type="submit"]:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        button[type="submit"]:disabled::before {
            display: none;
        }

        /* Lien retour */
        .back-link {
            margin-top: 1.5rem;
            text-align: center;
        }

        .back-link a {
            color: #6b7280;
            text-decoration: none;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: color 0.3s ease;
        }

        .back-link a:hover {
            color: #E8513E;
        }

        /* Critères du mot de passe */
        .password-requirements {
            margin-top: 0.5rem;
            padding: 0.75rem;
            background-color: #f9fafb;
            border-radius: 0.75rem;
            font-size: 0.7rem;
        }

        .requirement {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.25rem;
            color: #6b7280;
            transition: color 0.3s ease;
        }

        .requirement.met {
            color: #22c55e;
        }

        .requirement svg {
            flex-shrink: 0;
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
        }

        /* Loading state */
        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
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
            <h1>Nouveau mot de passe</h1>
            <p>Choisissez un mot de passe sécurisé</p>
        </div>

        <div class="body">
            @if(session('status'))
                <div class="status-message status-success">
                    <svg class="status-icon" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="status-message status-error">
                    <svg class="status-icon" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.reset.post') }}" id="resetForm">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="form-group">
                    <label for="email_display">Compte email</label>
                    <div class="input-wrapper">
                        <div class="input-icon">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                            </svg>
                        </div>
                        <input 
                            type="email" 
                            id="email_display"
                            value="{{ $email }}" 
                            disabled
                            style="background-color: #f3f4f6; cursor: not-allowed; opacity: 0.7;">
                    </div>
                    <p style="font-size: 0.7rem; color: #6b7280; margin-top: 0.25rem;">
                        Le mot de passe sera changé pour ce compte
                    </p>
                </div>

                <div class="form-group">
                    <label for="password">Nouveau mot de passe</label>
                    <div class="input-wrapper">
                        <div class="input-icon">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <input 
                            type="password" 
                            name="password" 
                            id="password"
                            placeholder="Entrez votre nouveau mot de passe" 
                            required>
                        <button type="button" class="toggle-password" data-target="password">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Barre de force du mot de passe -->
                    <div class="password-strength">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <div class="strength-text" id="strengthText"></div>
                    </div>

                    <!-- Critères du mot de passe -->
                    <div class="password-requirements" id="passwordRequirements">
                        <div class="requirement" id="reqLength">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Au moins 8 caractères</span>
                        </div>
                        <div class="requirement" id="reqUpper">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Au moins une majuscule</span>
                        </div>
                        <div class="requirement" id="reqNumber">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Au moins un chiffre</span>
                        </div>
                        <div class="requirement" id="reqSpecial">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Au moins un caractère spécial (!@#$%^&*)</span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirmer le mot de passe</label>
                    <div class="input-wrapper">
                        <div class="input-icon">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <input 
                            type="password" 
                            name="password_confirmation" 
                            id="password_confirmation"
                            placeholder="Confirmez votre nouveau mot de passe" 
                            required>
                        <button type="button" class="toggle-password" data-target="password_confirmation">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    <div class="error-message" id="confirmError" style="display: none;">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Les mots de passe ne correspondent pas</span>
                    </div>
                </div>

                <button type="submit" id="submitBtn">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span>Réinitialiser le mot de passe</span>
                </button>
            </form>
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
    
    // Évaluation de la force du mot de passe
    function checkPasswordStrength(password) {
        let strength = 0;
        
        // Longueur
        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;
        
        // Majuscule
        if (/[A-Z]/.test(password)) strength++;
        
        // Chiffre
        if (/[0-9]/.test(password)) strength++;
        
        // Caractère spécial
        if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength++;
        
        return Math.min(strength, 5);
    }
    
    function getStrengthText(strength) {
        const texts = ['Très faible', 'Faible', 'Moyen', 'Fort', 'Très fort', 'Excellent'];
        return texts[strength];
    }
    
    function getStrengthColor(strength) {
        const colors = ['#ef4444', '#f59e0b', '#eab308', '#84cc16', '#22c55e', '#10b981'];
        return colors[strength];
    }
    
    function updatePasswordStrength() {
        const password = document.getElementById('password').value;
        const strength = checkPasswordStrength(password);
        const fill = document.getElementById('strengthFill');
        const text = document.getElementById('strengthText');
        
        const percentage = (strength / 5) * 100;
        fill.style.width = percentage + '%';
        fill.style.backgroundColor = getStrengthColor(strength);
        text.textContent = getStrengthText(strength);
        
        // Mettre à jour les critères
        updateRequirement('reqLength', password.length >= 8);
        updateRequirement('reqUpper', /[A-Z]/.test(password));
        updateRequirement('reqNumber', /[0-9]/.test(password));
        updateRequirement('reqSpecial', /[!@#$%^&*(),.?":{}|<>]/.test(password));
    }
    
    function updateRequirement(elementId, met) {
        const element = document.getElementById(elementId);
        if (met) {
            element.classList.add('met');
            element.querySelector('svg').innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>';
        } else {
            element.classList.remove('met');
            element.querySelector('svg').innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>';
        }
    }
    
    function checkPasswordMatch() {
        const password = document.getElementById('password').value;
        const confirm = document.getElementById('password_confirmation').value;
        const errorDiv = document.getElementById('confirmError');
        
        if (confirm.length > 0 && password !== confirm) {
            errorDiv.style.display = 'flex';
            return false;
        } else {
            errorDiv.style.display = 'none';
            return true;
        }
    }
    
    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            
            // Changer l'icône
            this.innerHTML = type === 'password' ? 
                '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>' :
                '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>';
        });
    });
    
    // Écouter les événements
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirmation');
    const form = document.getElementById('resetForm');
    const submitBtn = document.getElementById('submitBtn');
    
    passwordInput.addEventListener('input', function() {
        updatePasswordStrength();
        checkPasswordMatch();
    });
    
    confirmInput.addEventListener('input', checkPasswordMatch);
    
    form.addEventListener('submit', function(e) {
        // Validation avant envoi
        const password = passwordInput.value;
        const confirm = confirmInput.value;
        
        if (!checkPasswordMatch()) {
            e.preventDefault();
            return;
        }
        
        if (password.length < 8) {
            e.preventDefault();
            showError('Le mot de passe doit contenir au moins 8 caractères');
            return;
        }
        
        // Afficher le loader
        submitBtn.disabled = true;
        const originalContent = submitBtn.innerHTML;
        submitBtn.innerHTML = '<div class="spinner"></div><span>Réinitialisation en cours...</span>';
        
        // Le formulaire sera soumis normalement
    });
    
    function showError(message) {
        const existingError = document.querySelector('.status-message');
        if (existingError && !existingError.classList.contains('status-success')) {
            existingError.remove();
        }
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'status-message status-error';
        errorDiv.innerHTML = `
            <svg class="status-icon" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <span>${message}</span>
        `;
        
        const body = document.querySelector('.body');
        const formElement = document.querySelector('form');
        body.insertBefore(errorDiv, formElement);
        
        setTimeout(() => {
            errorDiv.style.opacity = '0';
            setTimeout(() => errorDiv.remove(), 300);
        }, 5000);
    }
    
    // Initialisation
    createBubbles();
    updatePasswordStrength();
    
    // Restaurer le bouton si la page est rechargée
    window.addEventListener('pageshow', function() {
        submitBtn.disabled = false;
        submitBtn.innerHTML = `
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <span>Réinitialiser le mot de passe</span>
        `;
    });
</script>

</body>
</html>