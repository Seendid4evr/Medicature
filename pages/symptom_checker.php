<?php
require_once '../includes/session.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Symptom Checker - Medicure</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <?php include '../includes/pwa_head.php'; ?>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* ── Layout ── */
        .checker-wrapper {
            max-width: 820px;
            margin: 2rem auto;
        }
        .page-header { text-align: center; margin-bottom: 1.5rem; }
        .page-header h1 { font-size: 1.75rem; margin-bottom: 0.4rem; }
        .page-header p  { color: #64748b; font-size: 0.95rem; }

        /* ── Disclaimer Banner ── */
        .disclaimer-banner {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            background: #fff3cd;
            border: 2px solid #f59e0b;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.25rem;
            font-size: 0.9rem;
            color: #78350f;
            line-height: 1.55;
        }
        .disclaimer-banner i {
            font-size: 1.4rem;
            color: #f59e0b;
            flex-shrink: 0;
            margin-top: 2px;
        }
        .disclaimer-banner strong { display: block; font-size: 1rem; margin-bottom: 0.2rem; color: #92400e; }

        /* ── Chat Container ── */
        .chat-container {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
            display: flex;
            flex-direction: column;
            height: 580px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        .chat-header {
            padding: 1.1rem 1.5rem;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-shrink: 0;
        }
        .chat-header i { font-size: 1.8rem; }
        .chat-header h2 { margin: 0; font-size: 1.15rem; }
        .chat-header span { font-size: 0.8rem; opacity: 0.85; }
        .status-dot {
            width: 9px; height: 9px;
            background: #4ade80;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
            animation: pulse 1.8s infinite;
        }
        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.4} }

        /* ── Quick Symptom Chips ── */
        .quick-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem 0.5rem;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            flex-shrink: 0;
        }
        .chip {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
            border-radius: 999px;
            padding: 0.3rem 0.85rem;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.15s;
            font-weight: 500;
        }
        .chip:hover { background: #0369a1; color: white; }

        /* ── Chat Body ── */
        .chat-body {
            flex-grow: 1;
            padding: 1.25rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            background: #f8fafc;
        }
        .message {
            max-width: 82%;
            padding: 0.9rem 1.1rem;
            border-radius: 14px;
            line-height: 1.6;
            font-size: 0.93rem;
            word-break: break-word;
        }
        .message.ai {
            align-self: flex-start;
            background: #fff;
            color: #1e293b;
            border: 1px solid #e2e8f0;
            border-bottom-left-radius: 2px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }
        .message.user {
            align-self: flex-end;
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            color: white;
            border-bottom-right-radius: 2px;
        }

        /* ── Severity coloring on AI messages ── */
        .message.ai.severity-emergency { border-color: #ef4444; background: #fff5f5; }
        .message.ai.severity-moderate  { border-color: #f59e0b; background: #fffbeb; }

        /* ── Inline "doctor preferred" badge inside AI message ── */
        .doctor-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
            border-radius: 8px;
            padding: 0.5rem 0.9rem;
            font-size: 0.82rem;
            font-weight: 600;
            margin: 0.6rem 0 0.4rem;
            width: 100%;
        }

        /* ── Loading ── */
        .loading-indicator {
            align-self: flex-start;
            display: none;
            color: #64748b;
            font-size: 0.88rem;
            padding: 0.4rem 0.8rem;
            background: #fff;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }
        .loading-indicator i { margin-right: 6px; }

        /* ── Input Area ── */
        .chat-input-area {
            padding: 1rem 1.25rem;
            background: #fff;
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 0.75rem;
            align-items: center;
            flex-shrink: 0;
        }
        .chat-input {
            flex-grow: 1;
            padding: 0.8rem 1.1rem;
            border: 2px solid #e2e8f0;
            border-radius: 999px;
            font-size: 0.95rem;
            transition: border-color 0.2s;
            font-family: inherit;
        }
        .chat-input:focus { outline: none; border-color: #3b82f6; }
        .send-btn {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            color: white;
            border: none;
            width: 46px; height: 46px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            transition: transform 0.2s, box-shadow 0.2s;
            flex-shrink: 0;
        }
        .send-btn:hover { transform: scale(1.08); box-shadow: 0 4px 12px rgba(59,130,246,0.4); }

        /* ── Markdown rendered ── */
        .message.ai h2 { font-size: 1rem; color: #1e3a8a; margin: 0.8rem 0 0.4rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.3rem; }
        .message.ai hr { border: none; border-top: 1px solid #e2e8f0; margin: 0.6rem 0; }
        .message.ai strong { color: #1e293b; }
        .message.ai em { color: #475569; }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container">
        <div class="checker-wrapper">
            <!-- Page Header -->
            <div class="page-header">
                <h1><i class="fas fa-stethoscope" style="color:#3b82f6;"></i> AI Symptom Checker</h1>
                <p>Describe your symptoms and get general medicine guidance — powered by our built-in medical knowledge base.</p>
            </div>

            <!-- PROMINENT DISCLAIMER -->
            <div class="disclaimer-banner">
                <i class="fas fa-triangle-exclamation"></i>
                <div>
                    <strong>A Doctor is Always the Better Choice</strong>
                    This tool provides general OTC (over-the-counter) medicine information only. It is <em>not</em> a substitute for professional medical advice, diagnosis, or treatment. For any serious, persistent, or worsening symptom — always visit a licensed doctor or hospital.
                </div>
            </div>

            <!-- Chat UI -->
            <div class="chat-container">
                <!-- Header -->
                <div class="chat-header">
                    <i class="fas fa-robot"></i>
                    <div>
                        <h2>Medicure Health AI</h2>
                        <span><span class="status-dot"></span>General Knowledge Mode — 20+ conditions covered</span>
                    </div>
                </div>

                <!-- Quick chips -->
                <div class="quick-chips">
                    <span class="chip" onclick="fillChip('I have fever')">🌡️ Fever</span>
                    <span class="chip" onclick="fillChip('I have a headache')">🤕 Headache</span>
                    <span class="chip" onclick="fillChip('I have acidity and heartburn')">🔥 Acidity</span>
                    <span class="chip" onclick="fillChip('I have a cold and runny nose')">🤧 Cold</span>
                    <span class="chip" onclick="fillChip('I have diarrhea and loose motion')">💧 Diarrhea</span>
                    <span class="chip" onclick="fillChip('I have nausea and vomiting')">😞 Nausea</span>
                    <span class="chip" onclick="fillChip('I have body pain and back pain')">💪 Body Pain</span>
                    <span class="chip" onclick="fillChip('I have a cough')">😮‍💨 Cough</span>
                </div>

                <!-- Messages -->
                <div class="chat-body" id="chatBody">
                    <div class="message ai">
                        Hello <strong><?php echo htmlspecialchars(getUserName()); ?></strong>! 👋
                        <br><br>
                        Tell me your symptoms and I'll suggest common medicines used in Bangladesh — along with when to see a doctor. You can also tap a quick chip above to get started.
                        <br><br>
                        <em>Remember: I provide general guidance only. A doctor is always the better choice.</em>
                    </div>
                    <div class="loading-indicator" id="loading">
                        <i class="fas fa-circle-notch fa-spin"></i> Checking medical knowledge base...
                    </div>
                </div>

                <!-- Input -->
                <div class="chat-input-area">
                    <input type="text" id="symptomInput" class="chat-input"
                           placeholder="e.g. I have fever and headache..."
                           onkeypress="handleEnter(event)" autocomplete="off">
                    <button class="send-btn" onclick="sendSymptoms()" title="Send">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div><!-- /chat-container -->
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/alarm.js"></script>
    <script>
        function fillChip(text) {
            document.getElementById('symptomInput').value = text;
            document.getElementById('symptomInput').focus();
        }

        // Full markdown → HTML renderer
        function parseFormatting(text) {
            // Escape HTML first to prevent XSS
            text = text
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');

            // HR
            text = text.replace(/^---$/gm, '<hr>');

            // Headings ## H2
            text = text.replace(/^##\s+(.+)$/gm, '<h2>$1</h2>');

            // Doctor banner trigger
            text = text.replace(/\[\!\]\s*([^\n]+)/g,
                '<div class="doctor-badge"><i class="fas fa-user-md"></i> $1</div>');

            // Bold **text**
            text = text.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');

            // Italic *text*
            text = text.replace(/\*([^*\n]+)\*/g, '<em>$1</em>');

            // Newlines
            text = text.replace(/\n/g, '<br>');

            return text;
        }

        function appendMessage(sender, text, severity) {
            const chatBody = document.getElementById('chatBody');
            const loading  = document.getElementById('loading');
            const msgDiv   = document.createElement('div');
            msgDiv.className = `message ${sender}`;

            if (sender === 'ai') {
                msgDiv.innerHTML = parseFormatting(text);
                if (severity === 'emergency') msgDiv.classList.add('severity-emergency');
                if (severity === 'moderate')  msgDiv.classList.add('severity-moderate');
            } else {
                msgDiv.textContent = text;
            }

            chatBody.insertBefore(msgDiv, loading);
            chatBody.scrollTop = chatBody.scrollHeight;
        }

        function handleEnter(e) {
            if (e.key === 'Enter') sendSymptoms();
        }

        function sendSymptoms() {
            const input = document.getElementById('symptomInput');
            const text  = input.value.trim();
            if (!text) return;

            appendMessage('user', text);
            input.value = '';

            const loading = document.getElementById('loading');
            loading.style.display = 'block';
            document.getElementById('chatBody').scrollTop = 99999;

            fetch('../api/check_symptoms.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ symptoms: text })
            })
            .then(res => res.json())
            .then(data => {
                loading.style.display = 'none';
                if (data.error) {
                    appendMessage('ai', 'I encountered an error accessing the medical database.');
                } else {
                    appendMessage('ai', data.response, data.severity || 'mild');
                }
            })
            .catch(err => {
                loading.style.display = 'none';
                appendMessage('ai', 'Sorry, I could not connect to the triage server at this time.');
                console.error(err);
            });
        }
    </script>
</body>
</html>
