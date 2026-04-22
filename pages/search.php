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
    <title>Search Medicines - Medicature</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <?php include '../includes/pwa_head.php'; ?>
    <style>
        .search-container {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
        }
        .search-input {
            width: 100%;
            padding: 1.5rem;
            font-size: 1.25rem;
            border: 2px solid #e2e8f0;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
        }
        .autocomplete-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: var(--radius);
            max-height: 400px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: var(--shadow-md);
            margin-top: 0.5rem;
        }
        .autocomplete-results.show {
            display: block;
        }
        .autocomplete-item {
            padding: 1rem;
            cursor: pointer;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s;
        }
        .autocomplete-item:last-child {
            border-bottom: none;
        }
        .autocomplete-item:hover {
            background-color: #f8fafc;
        }
        .autocomplete-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .autocomplete-brand {
            font-size: 1.1rem;
            font-weight: bold;
            color: #333;
        }
        .autocomplete-generic {
            display: block;
            color: var(--primary-color);
            margin-top: 0.25rem;
            font-size: 0.9em;
        }
        .autocomplete-meta {
            color: #666;
            font-size: 0.85em;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container">
        <div class="page-header" style="text-align: center; margin-bottom: 3rem;">
            <h1>Lookup Medicine Information</h1>
            <p>Search our extensive offline database to learn about your medications, indications, and side-effects.</p>
        </div>
        
        <div class="search-container">
            <input type="text" id="medicineSearch" class="search-input" placeholder="Search by brand name or generic (e.g., Napa, Paracetamol)..." autocomplete="off" autofocus>
            <div id="searchResults" class="autocomplete-results"></div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/alarm.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('medicineSearch');
            const resultsContainer = document.getElementById('searchResults');
            let timeout = null;

            searchInput.addEventListener('input', function() {
                clearTimeout(timeout);
                const query = this.value.trim();
                
                if (query.length < 2) {
                    resultsContainer.classList.remove('show');
                    return;
                }

                timeout = setTimeout(() => {
                    fetch(`../api/search_medicines.php?q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.length === 0) {
                                resultsContainer.innerHTML = '<div class="autocomplete-item"><span style="color:#666;">No medicines found matching that name.</span></div>';
                                resultsContainer.classList.add('show');
                                return;
                            }

                            resultsContainer.innerHTML = '';
                            data.forEach(med => {
                                const div = document.createElement('div');
                                div.className = 'autocomplete-item';
                                div.innerHTML = `
                                    <div class="autocomplete-item-header">
                                        <span class="autocomplete-brand">${med.brand_name} ${med.strength ? '(' + med.strength + ')' : ''}</span>
                                        <span class="badge" style="background:#e0f2fe;color:#0369a1;font-size:0.75rem;padding:0.2rem 0.6rem;">View Details &rarr;</span>
                                    </div>
                                    <span class="autocomplete-generic">${med.generic || 'Unknown Generic'}</span>
                                    <div class="autocomplete-meta">${med.dosage_form} | ${med.manufacturer}</div>
                                `;
                                div.addEventListener('click', () => {
                                    window.location.href = `medicine_details.php?id=${med.id}`;
                                });
                                resultsContainer.appendChild(div);
                            });
                            resultsContainer.classList.add('show');
                        })
                        .catch(err => {
                            console.error('Error fetching medicines:', err);
                        });
                }, 300); // 300ms debounce
            });

            // Close results when clicking outside
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
                    resultsContainer.classList.remove('show');
                }
            });
        });
    </script>
</body>
</html>
