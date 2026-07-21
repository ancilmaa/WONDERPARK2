
<div class="chat-window" id="chatWindow">
    <div class="chat-head">
        <div>
            Wonder Park
            <small>AI chatbot</small>
        </div>
        <span style="cursor:pointer;" onclick="document.getElementById('chatWindow').classList.remove('open')">&times;</span>
    </div>
    <div class="chat-body" id="chatMessages">
        <div class="chat-row bot">
            <span class="chat-avatar">
                <img src="<?php echo e(asset('images/wonderpark1logo.png')); ?>" alt="Wonder Park">
            </span>
            <div class="bubble bot">
                Hi! I'm the Wonder Park assistant 👋 Ask me about our packages, pricing, or how booking works — happy to help you plan your visit!
            </div>
        </div>
        <div id="chatQuickActions">
            <span class="quick" data-msg="What's included in Dino Adventure?">What's included in Dino Adventure?</span>
            <span class="quick" data-msg="How much is RollerFever?">How much is RollerFever?</span>
            <span class="quick" data-msg="How much is Field of Rides?">How much is Field of Rides?</span>
        </div>
    </div>
    <div class="chat-input">
        <input type="text" id="chatInput" placeholder="Type a message...">
        <button type="button" id="chatSend">&#10148;</button>
    </div>
</div>
<button class="chat-fab" onclick="document.getElementById('chatWindow').classList.add('open')">&#128172;</button>

<script src="<?php echo e(asset('js/reks-assist.js')); ?>"></script>
<?php /**PATH C:\xampp\htdocs\WONDERPARK\WONDERPARK2\laravel-app\resources\views/partials/chat-widget.blade.php ENDPATH**/ ?>