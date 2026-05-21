<?php

function validatePasswordStrength($password, &$errors = []) {
    $isValid = true;
    
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
        $isValid = false;
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter (A-Z).";
        $isValid = false;
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter (a-z).";
        $isValid = false;
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number (0-9).";
        $isValid = false;
    }
    
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $errors[] = "Password must contain at least one special character (!@#$%^&*).";
        $isValid = false;
    }
    
    return $isValid;
}

function getPasswordStrength($password) {
    $score = 0;
    
    if (strlen($password) >= 8) $score++;
    if (preg_match('/[A-Z]/', $password)) $score++;
    if (preg_match('/[a-z]/', $password)) $score++;
    if (preg_match('/[0-9]/', $password)) $score++;
    if (preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) $score++;
    
    if ($score <= 2) return ['level' => 'weak', 'text' => 'Weak', 'color' => '#ef4444'];
    if ($score <= 4) return ['level' => 'medium', 'text' => 'Medium', 'color' => '#f59e0b'];
    return ['level' => 'strong', 'text' => 'Strong', 'color' => '#10b981'];
}

function getPasswordRequirementsHTML() {
    return '
    <div class="password-requirements" style="font-size: 0.7rem; margin-top: 5px;">
        <div class="req-length req-item" data-rule="length">✗ At least 8 characters</div>
        <div class="req-uppercase req-item" data-rule="uppercase">✗ At least 1 uppercase letter (A-Z)</div>
        <div class="req-lowercase req-item" data-rule="lowercase">✗ At least 1 lowercase letter (a-z)</div>
        <div class="req-number req-item" data-rule="number">✗ At least 1 number (0-9)</div>
        <div class="req-special req-item" data-rule="special">✗ At least 1 special character (!@#$%^&*)</div>
    </div>
    ';
}

function getPasswordStrengthJS() {
    return '
    <script>
    function checkPasswordStrength(password) {
        let score = 0;
        let rules = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
        };
        
        // Update requirement checkmarks
        for (let rule in rules) {
            const element = document.querySelector(`.req-${rule}`);
            if (element) {
                if (rules[rule]) {
                    element.innerHTML = "✓ " + element.innerHTML.substring(2);
                    element.style.color = "#10b981";
                } else {
                    element.innerHTML = "✗ " + element.innerHTML.substring(2);
                    element.style.color = "#ef4444";
                }
            }
        }
        
        // Calculate strength
        if (rules.length) score++;
        if (rules.uppercase) score++;
        if (rules.lowercase) score++;
        if (rules.number) score++;
        if (rules.special) score++;
        
        let strengthText = "";
        let strengthColor = "";
        let strengthBar = "";
        
        if (score <= 2) {
            strengthText = "Weak";
            strengthColor = "#ef4444";
            strengthBar = "33%";
        } else if (score <= 4) {
            strengthText = "Medium";
            strengthColor = "#f59e0b";
            strengthBar = "66%";
        } else {
            strengthText = "Strong";
            strengthColor = "#10b981";
            strengthBar = "100%";
        }
        
        const strengthDisplay = document.getElementById("password-strength-display");
        if (strengthDisplay) {
            strengthDisplay.innerHTML = `Password Strength: <span style="color: ${strengthColor}; font-weight: bold;">${strengthText}</span>`;
        }
        
        const strengthBarElement = document.getElementById("password-strength-bar");
        if (strengthBarElement) {
            strengthBarElement.style.width = strengthBar;
            strengthBarElement.style.backgroundColor = strengthColor;
        }
        
        return rules.length && rules.uppercase && rules.lowercase && rules.number && rules.special;
    }
    
    function validatePasswordForm() {
        const password = document.getElementById("new_password").value;
        const isValid = checkPasswordStrength(password);
        if (!isValid) {
            alert("Please make sure your password meets all requirements before submitting.");
            return false;
        }
        return true;
    }
    
    // Attach event listener when DOM is ready
    document.addEventListener("DOMContentLoaded", function() {
        const passwordInput = document.getElementById("new_password");
        if (passwordInput) {
            passwordInput.addEventListener("keyup", function() {
                checkPasswordStrength(this.value);
            });
            // Initial check
            checkPasswordStrength(passwordInput.value);
        }
    });
    </script>
    
    <style>
    .password-strength-container {
        margin-top: 8px;
        margin-bottom: 10px;
    }
    .password-strength-bar-bg {
        background-color: #e2e8f0;
        height: 5px;
        border-radius: 5px;
        margin-top: 5px;
    }
    .password-strength-bar {
        width: 0%;
        height: 5px;
        border-radius: 5px;
        transition: 0.3s;
    }
    .password-requirements {
        background: #f8fafc;
        padding: 10px;
        border-radius: 8px;
        margin-top: 8px;
        border: 1px solid #e2e8f0;
    }
    .req-item {
        margin: 3px 0;
        transition: 0.2s;
    }
    </style>
    ';
}
?>