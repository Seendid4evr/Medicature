<?php
require_once '../includes/session.php';
require_once '../includes/auth.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Calculators - Medicature</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <?php include '../includes/pwa_head.php'; ?>
    <style>
        .calculator-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        .calculator-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 2rem;
            box-shadow: var(--shadow-md);
        }
        .calculator-card h2 {
            margin-bottom: 1.5rem;
            color: var(--primary-color);
            border-bottom: 2px solid #eee;
            padding-bottom: 0.5rem;
        }
        .result-box {
            margin-top: 1.5rem;
            padding: 1rem;
            border-radius: 4px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            display: none;
            text-align: center;
        }
        .result-box.show {
            display: block;
        }
        .result-value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
            margin: 0.5rem 0;
        }
        .result-label {
            font-weight: 600;
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Health Calculators</h1>
            <p>Track your vitals using our simple tools</p>
        </div>
        
        <div class="calculator-grid">
            <!-- BMI Calculator -->
            <div class="calculator-card">
                <h2>BMI Calculator</h2>
                <form id="bmiForm" onsubmit="calculateBMI(event)">
                    <div class="form-group">
                        <label for="bmiHeight">Height (cm)</label>
                        <input type="number" id="bmiHeight" required min="50" max="300" step="0.1" placeholder="e.g. 170">
                    </div>
                    <div class="form-group">
                        <label for="bmiWeight">Weight (kg)</label>
                        <input type="number" id="bmiWeight" required min="10" max="500" step="0.1" placeholder="e.g. 70">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Calculate BMI</button>
                </form>
                
                <div id="bmiResult" class="result-box">
                    <div>Your BMI is</div>
                    <div id="bmiValue" class="result-value"></div>
                    <div id="bmiCategory" class="result-label"></div>
                </div>
            </div>

            <!-- Ideal Weight Calculator -->
            <div class="calculator-card">
                <h2>Ideal Weight Calculator</h2>
                <form id="iwbForm" onsubmit="calculateIdealWeight(event)">
                    <div class="form-group">
                        <label for="iwbGender">Gender</label>
                        <select id="iwbGender" required>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="iwbHeight">Height (cm)</label>
                        <input type="number" id="iwbHeight" required min="140" max="250" step="0.1" placeholder="e.g. 170">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Calculate Ideal Weight</button>
                </form>

                <div id="iwbResult" class="result-box">
                    <div>Your Ideal Weight Range (Devine formula) is</div>
                    <div id="iwbValue" class="result-value"></div>
                    <div class="result-label">kg</div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/alarm.js"></script>
    <script>
        function calculateBMI(e) {
            e.preventDefault();
            const heightCm = parseFloat(document.getElementById('bmiHeight').value);
            const weightKg = parseFloat(document.getElementById('bmiWeight').value);
            
            if (heightCm > 0 && weightKg > 0) {
                const heightM = heightCm / 100;
                const bmi = weightKg / (heightM * heightM);
                
                document.getElementById('bmiValue').textContent = bmi.toFixed(1);
                
                let category = '';
                let color = '';
                if (bmi < 18.5) { category = 'Underweight'; color = '#eab308'; }
                else if (bmi < 25) { category = 'Normal weight'; color = '#22c55e'; }
                else if (bmi < 30) { category = 'Overweight'; color = '#f97316'; }
                else { category = 'Obese'; color = '#ef4444'; }
                
                const catElement = document.getElementById('bmiCategory');
                catElement.textContent = category;
                catElement.style.color = color;
                
                document.getElementById('bmiResult').classList.add('show');
            }
        }

        function calculateIdealWeight(e) {
            e.preventDefault();
            const heightCm = parseFloat(document.getElementById('iwbHeight').value);
            const gender = document.getElementById('iwbGender').value;
            
            if (heightCm > 0) {
                // Devine Formula (requires height > 5 feet / 152.4 cm for strict accuracy, but commonly approximated below)
                // Male: 50.0 kg + 2.3 kg per inch over 5 feet
                // Female: 45.5 kg + 2.3 kg per inch over 5 feet
                
                const baseWeight = gender === 'male' ? 50.0 : 45.5;
                const inchesOver5Feet = (heightCm - 152.4) / 2.54;
                
                let idealWeight = baseWeight;
                if (inchesOver5Feet > 0) {
                    idealWeight += 2.3 * inchesOver5Feet;
                }
                
                // Provide a +- 10% range
                const minWeight = idealWeight * 0.9;
                const maxWeight = idealWeight * 1.1;

                document.getElementById('iwbValue').textContent = `${minWeight.toFixed(1)} - ${maxWeight.toFixed(1)}`;
                document.getElementById('iwbResult').classList.add('show');
            }
        }
    </script>
</body>
</html>
