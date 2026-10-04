<!-- Terms & Conditions -->
<dialog id="termsModal" class="legal-modal">
    <div class="legal-head">
        <h3>Terms &amp; Conditions</h3>
        <button type="button" class="legal-close" data-close aria-label="Close">&times;</button>
    </div>
    <div class="legal-body">
        <p class="legal-updated">Last updated: October 4, 2026</p>

        <h4>1. Acceptance of Terms</h4>
        <p>By accessing or using this system, you agree to be bound by these Terms &amp; Conditions. If you do not agree, please do not use the system.</p>

        <h4>2. Purpose of the System</h4>
        <p>This system is used by WonderPark Amusement Com Inc., Lipa Branch to manage bookings, inventory, and front-desk operations for Roller Fever, Dino Adventure, and the Field of Rides.</p>

        <h4>3. User Accounts</h4>
        <p>You are responsible for keeping your username and password confidential and for all activities under your account. Notify the administrator immediately if you suspect unauthorized access. Sharing of accounts is not allowed.</p>

        <h4>4. Acceptable Use</h4>
        <p>You agree not to: (a) access data or features beyond your assigned role; (b) attempt to bypass security or interfere with the system; (c) enter false, misleading, or fraudulent information; or (d) use the system for any unlawful purpose.</p>

        <h4>5. Bookings and Transactions</h4>
        <p>Bookings, payments, and ticketing records entered in the system are subject to the company's park policies, including rates, schedules, refunds, and ride safety rules, which may change without prior notice.</p>

        <h4>6. Intellectual Property</h4>
        <p>All content, branding, and software in this system belong to WonderPark Amusement Com Inc. or its licensors. You may not copy, modify, or distribute any part of it without written permission.</p>

        <h4>7. Suspension or Termination</h4>
        <p>The company may suspend or terminate any account that violates these Terms or that poses a risk to the security of the system.</p>

        <h4>8. Limitation of Liability</h4>
        <p>The system is provided "as is." The company is not liable for any loss arising from downtime, errors, or unauthorized access caused by failure to protect your login credentials.</p>

        <h4>9. Changes to These Terms</h4>
        <p>We may update these Terms from time to time. Continued use of the system means you accept the updated Terms.</p>

        <h4>10. Contact</h4>
        <p>For questions, contact the Lipa Branch administrator at <strong>your-email@example.com</strong>.</p>
    </div>
    <div class="legal-foot">
        <label class="legal-agree">
            <input type="checkbox">
            <span>I have read and agree to the Terms &amp; Conditions</span>
        </label>
        <button type="button" class="btn btn-primary legal-accept" data-accept disabled>I Accept</button>
    </div>
</dialog>

<!-- Privacy Notice -->
<dialog id="privacyModal" class="legal-modal">
    <div class="legal-head">
        <h3>Privacy Notice</h3>
        <button type="button" class="legal-close" data-close aria-label="Close">&times;</button>
    </div>
    <div class="legal-body">
        <p class="legal-updated">Last updated: October 4, 2026</p>
        <p>WonderPark Amusement Com Inc., Lipa Branch respects your privacy and processes personal data in accordance with the <strong>Data Privacy Act of 2012 (Republic Act No. 10173)</strong>.</p>

        <h4>1. Information We Collect</h4>
        <p>Full name, username, email address, age, booking and transaction details, and, if you use Google or Facebook login, the basic profile information (name and email) that those services share with us. We also record technical data such as login activity and IP address for security.</p>

        <h4>2. How We Use Your Information</h4>
        <p>To create and manage your account, process bookings and payments, manage inventory and operations, send account-related notices (such as password resets), prevent fraud, and improve our services.</p>

        <h4>3. Legal Basis</h4>
        <p>We process your data based on your consent, the performance of a contract or service, and our legitimate business and legal obligations.</p>

        <h4>4. Sharing of Information</h4>
        <p>We do not sell your personal data. It is shared only with authorized personnel, trusted service providers (e.g., payment or email providers), or government authorities when required by law.</p>

        <h4>5. Storage and Security</h4>
        <p>Your data is stored securely and protected by reasonable organizational, physical, and technical measures, including encrypted passwords and access controls. Data is retained only as long as necessary for the purposes stated above or as required by law.</p>

        <h4>6. Your Rights</h4>
        <p>Under the Data Privacy Act, you have the right to be informed, to access, to correct, to object, to erasure or blocking, to data portability, to file a complaint with the <strong>National Privacy Commission (NPC)</strong>, and to claim damages where applicable.</p>

        <h4>7. Cookies</h4>
        <p>We use essential cookies to keep you signed in and to protect your session. We do not use them for advertising.</p>

        <h4>8. Contact Our Data Protection Officer</h4>
        <p>To exercise your rights or ask questions, contact us at <strong>wonderparkamusement@example.com</strong>.</p>
    </div>
    <div class="legal-foot">
        <label class="legal-agree">
            <input type="checkbox">
            <span>I have read and agree to the Privacy Notice</span>
        </label>
        <button type="button" class="btn btn-primary legal-accept" data-accept disabled>I Accept</button>
    </div>
</dialog>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var formTerms = document.getElementById('terms'); // checkbox sa register form (wala sa login)

        document.querySelectorAll('[data-open]').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                var dlg = document.getElementById(link.dataset.open);
                var cb  = dlg.querySelector('.legal-agree input');
                var btn = dlg.querySelector('[data-accept]');
                cb.checked = formTerms ? formTerms.checked : false;
                btn.disabled = !cb.checked;
                dlg.showModal();
                dlg.querySelector('.legal-body').scrollTop = 0;
            });
        });

        document.querySelectorAll('.legal-modal').forEach(function (dlg) {
            var cb  = dlg.querySelector('.legal-agree input');
            var btn = dlg.querySelector('[data-accept]');

            dlg.querySelectorAll('[data-close]').forEach(function (b) {
                b.addEventListener('click', function () { dlg.close(); });
            });

            cb.addEventListener('change', function () { btn.disabled = !cb.checked; });

            btn.addEventListener('click', function () {
                if (formTerms) formTerms.checked = true; // i-tick ang checkbox sa register form
                dlg.close();
            });

            // close kapag nag-click sa dark backdrop
            dlg.addEventListener('click', function (e) {
                if (e.target === dlg) dlg.close();
            });
        });
    });
</script>