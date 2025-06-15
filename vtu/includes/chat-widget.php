<div class="chat-widget" style="position:fixed;bottom:20px;right:20px;width:350px;background:#181c3a;border-radius:1.5rem;box-shadow:0 5px 24px #FF007F;z-index:1000;display:flex;flex-direction:column;max-height:500px;font-family:'Audiowide','Press Start 2P',Arial,sans-serif;">
    <!-- Remove header, only show chat body -->
    <div class="chat-body" id="chat-body" style="display:none;flex-direction:column;flex-grow:1;padding:18px;overflow-y:auto;background:#181c3a;">
        <div class="chat-messages" id="chat-messages" style="flex-grow:1;overflow-y:auto;margin-bottom:15px;"></div>
        <div class="chat-input" style="display:flex;gap:10px;">
            <input type="text" id="chat-message" placeholder="Type your message..." style="flex-grow:1;padding:10px;border:2px solid #FFA500;border-radius:20px;background:#181c3a;color:#C0C0C0;font-family:'Audiowide',Arial,sans-serif;">
            <button onclick="sendMessage()" style="background:#FF007F;color:#fff;border:none;border-radius:20px;padding:0 18px;cursor:pointer;font-family:'Press Start 2P',Arial,sans-serif;box-shadow:0 2px 8px #FFA500;transition:background 0.2s;">Send</button>
        </div>
    </div>
</div>

<!-- Floating Chatbot Icon -->
<div id="chatbot-fab" onclick="toggleChat()" style="position:fixed;bottom:30px;right:30px;width:56px;height:56px;background:linear-gradient(135deg,#FF007F 60%,#FFA500 100%);border-radius:50%;box-shadow:0 4px 16px #FF007F;z-index:1100;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:box-shadow 0.2s;">
    <i class="fas fa-robot" style="font-size:2rem;color:#fff;text-shadow:0 0 8px #FFA500;"></i>
</div>

<style>
.chat-widget {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 350px;
    background: #181c3a;
    border-radius: 1.5rem;
    box-shadow: 0 5px 24px #FF007F;
    z-index: 1000;
    display: flex;
    flex-direction: column;
    max-height: 500px;
    font-family: 'Audiowide', 'Press Start 2P', Arial, sans-serif;
}

.chat-body {
    display: none;
    flex-direction: column;
    flex-grow: 1;
    padding: 18px;
    overflow-y: auto;
    background: #181c3a;
}

.chat-messages {
    flex-grow: 1;
    overflow-y: auto;
    margin-bottom: 15px;
}

.message {
    margin-bottom: 10px;
    padding: 8px 12px;
    border-radius: 18px;
    max-width: 80%;
    font-family: 'Audiowide', Arial, sans-serif;
}

.user-message {
    background: #FF007F;
    color: #fff;
    margin-left: auto;
    border-bottom-right-radius: 5px;
    box-shadow: 0 2px 8px #FFA500;
}

.bot-message {
    background: #181c3a;
    color: #FFA500;
    margin-right: auto;
    border-bottom-left-radius: 5px;
    box-shadow: 0 2px 8px #FF007F;
}

.chat-input {
    display: flex;
    gap: 10px;
}

.chat-input input {
    flex-grow: 1;
    padding: 10px;
    border: 2px solid #FFA500;
    border-radius: 20px;
    background: #181c3a;
    color: #C0C0C0;
    font-family: 'Audiowide', Arial, sans-serif;
}

.chat-input button {
    background: #FF007F;
    color: white;
    border: none;
    border-radius: 20px;
    padding: 0 18px;
    cursor: pointer;
    font-family: 'Press Start 2P', Arial, sans-serif;
    box-shadow: 0 2px 8px #FFA500;
    transition: background 0.2s;
}

/* Floating Action Button (FAB) styles */
#chatbot-fab {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #FF007F 60%, #FFA500 100%);
    border-radius: 50%;
    box-shadow: 0 4px 16px #FF007F;
    z-index: 1100;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: box-shadow 0.2s;
}

#chatbot-fab i {
    font-size: 2rem;
    color: #fff;
    text-shadow: 0 0 8px #FFA500;
}
</style>

<script>
let chatSessionId = null;
let sessionToken = null;
let pollInterval = null;

// Initialize chat
function initChat() {
    fetch('/api/chat/start', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            user_id: <?php echo $_SESSION['user_id'] ?? 'null'; ?>
        })
    })
    .then(response => response.json())
    .then data => {
        chatSessionId = data.session_id;
        sessionToken = data.session_token;
        
        // Start polling for messages
        pollInterval = setInterval(pollMessages, 3000);
        
        // Load any existing messages
        loadChatHistory();
    });
}

function toggleChat() {
    const chatWidget = document.querySelector('.chat-widget');
    const fab = document.getElementById('chatbot-fab');
    if (chatWidget.style.display === 'block') {
        chatWidget.style.display = 'none';
        fab.style.display = 'flex';
    } else {
        chatWidget.style.display = 'block';
        fab.style.display = 'none';
        // Show chat body
        document.getElementById('chat-body').style.display = 'flex';
    }
}

function sendMessage() {
    const input = document.getElementById('chat-message');
    const message = input.value.trim();
    
    if (message && chatSessionId) {
        // Add message to UI immediately
        addMessage('user', message);
        input.value = '';
        
        // Send to server
        fetch('/api/chat/message', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                session_id: chatSessionId,
                message: message
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.response) {
                addMessage('bot', data.response);
            }
        });
    }
}

function addMessage(sender, text) {
    const messagesDiv = document.getElementById('chat-messages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${sender}-message`;
    messageDiv.textContent = text;
    messagesDiv.appendChild(messageDiv);
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

function loadChatHistory() {
    if (!chatSessionId) return;
    
    fetch(`/api/chat/history?session_id=${chatSessionId}`)
    .then(response => response.json())
    .then(messages => {
        const messagesDiv = document.getElementById('chat-messages');
        messagesDiv.innerHTML = '';
        
        messages.forEach(msg => {
            addMessage(msg.sender, msg.message);
        });
    });
}

function pollMessages() {
    if (!chatSessionId || document.getElementById('chat-body').style.display === 'flex') return;
    
    fetch(`/api/chat/poll?session_id=${chatSessionId}`)
    .then(response => response.json())
    .then(data => {
        if (data.new_messages > 0) {
            document.getElementById('unread-count').textContent = data.new_messages;
        }
    });
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', initChat);

// Hide chat widget by default, show icon
window.addEventListener('DOMContentLoaded', function() {
    document.querySelector('.chat-widget').style.display = 'none';
    document.getElementById('chatbot-fab').style.display = 'flex';
    document.getElementById('chat-body').style.display = 'none';
});
</script>