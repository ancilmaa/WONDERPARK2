<?php $__env->startSection('title', 'Waiver'); ?>

<?php $__env->startSection('content'); ?>
<style>
* { box-sizing: border-box; }
html, body { margin: 0; padding: 0; }

.waiver-fullscreen {
    position: fixed;
    inset: 0;
    width: 100vw;
    height: 100vh;
    background: #fff;
    display: flex;
    flex-direction: column;
    z-index: 999;
}

.waiver-header {
    flex: 0 0 auto;
    padding: 2rem 3rem 1rem;
}

.waiver-scroll-area {
    flex: 1 1 auto;
    min-height: 0;
    padding: 0 3rem;
    display: flex;
    flex-direction: column;
}

.waiver-box-tall{
    flex: 1 1 auto;
    min-height: 0;
    overflow-y: auto;
    line-height: 1.6;
    border: 1px solid #e2e2e2;
    border-radius: 8px;
    padding: 1.5rem 1.75rem;
}
.waiver-box-tall h5{
    margin: 1.25rem 0 .35rem;
    font-size: .95rem;
}
.waiver-box-tall h5:first-of-type{ margin-top: 0; }

.waiver-footer {
    flex: 0 0 auto;
    padding: 1.25rem 3rem 2rem;
}

.u-btn:disabled {
    background-color: #cccccc;
    color: #888888;
    cursor: not-allowed;
    opacity: 0.7;
}
.u-btn:not(:disabled) {
    background-color: #c44484;
    color: #fff;
    cursor: pointer;
    opacity: 1;
}

/* --- Responsive: tablet & mobile --- */
@media (max-width: 900px) {
    .waiver-header { padding: 1.5rem 1.5rem 0.75rem; }
    .waiver-scroll-area { padding: 0 1.5rem; }
    .waiver-footer { padding: 1rem 1.5rem 1.5rem; }
}

@media (max-width: 480px) {
    .waiver-header { padding: 1.25rem 1rem 0.5rem; }
    .waiver-scroll-area { padding: 0 1rem; }
    .waiver-footer { padding: 0.85rem 1rem 1.25rem; }
    .waiver-box-tall { padding: 1.1rem 1.1rem; font-size: 0.92rem; }
    .u-card h4 { font-size: 1.05rem; }
}
</style>

<div class="waiver-fullscreen">

    <div class="waiver-header">
        <div class="u-card">
            <h4>Liability Waiver &amp; Data Privacy Consent</h4>
            <p>Required before confirming any <?php echo e($waiver['park_name']); ?> booking — covers Dino Adventure, RollerFever, and Field of Rides.</p>
        </div>
    </div>

    <div class="waiver-scroll-area">
        <div class="waiver-box waiver-box-tall">
            <p style="text-align:center; font-weight:700; margin-top:0;">
                AMUSEMENT PARK LIABILITY WAIVER
            </p>
            <p style="text-align:center; font-weight:600; margin-bottom:1rem;">
                RELEASE OF LIABILITY, WAIVER OF CLAIMS, ASSUMPTION OF RISKS,<br>
                DATA PRIVACY CONSENT, AND INDEMNITY AGREEMENT
            </p>

            <p><?php echo e($waiver['intro']); ?></p>

            <?php $__currentLoopData = $waiver['sections']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <h5><?php echo e($section['title']); ?></h5>
                <p><?php echo e($section['body']); ?></p>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <p>
                The undersigned acknowledges that they have read this agreement, understand its
                contents, and voluntarily agree to its terms.
            </p>
        </div>
    </div>

    <div class="waiver-footer">
        <form method="POST" action="<?php echo e(route('user.waiver.store')); ?>">
            <?php echo csrf_field(); ?>

            <div class="checkline">
                <input
                    type="checkbox"
                    name="agree"
                    id="agree"
                    value="1"
                    <?php echo e(old('agree') ? 'checked' : ''); ?>

                    style="margin-top:2px;"
                    required
                    onchange="document.getElementById('waiverSubmitBtn').disabled = !this.checked;"
                >
                <label for="agree">I acknowledge that I have read and fully understand this waiver — including the data privacy consent — and I voluntarily agree to its terms and conditions on behalf of myself and any minors under my supervision.</label>
            </div>

            <button type="submit" class="u-btn" id="waiverSubmitBtn" disabled style="width:100%; margin-top: 0.75rem;">I Accept</button>
        </form>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkbox = document.getElementById('agree');
        const btn = document.getElementById('waiverSubmitBtn');
        btn.disabled = !checkbox.checked;
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.waiver-only', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\REKS\REKS\laravel-app\resources\views/user/waiver.blade.php ENDPATH**/ ?>