<?php $__env->startSection('title', 'Account'); ?>
<?php $__env->startSection('page-title', 'Account'); ?>
<?php $__env->startSection('page-subtitle', 'Manage your WonderPark account'); ?>

<?php $__env->startSection('body-class', 'page-uniform'); ?>

<?php $__env->startSection('content'); ?>

    <?php
        $avatarChoices = [
            'rose'   => ['#FF5C85', '#B82850'],
            'gold'   => ['#FFC948', '#C77E0E'],
            'teal'   => ['#12B5A6', '#0C8B80'],
            'ink'    => ['#3B3350', '#171126'],
            'blue'   => ['#5B8CFF', '#2E52C7'],
        ];
        $currentAvatar = $user->avatar_theme ?? 'rose';
        [$avatarA, $avatarB] = $avatarChoices[$currentAvatar] ?? $avatarChoices['rose'];
        $hasPhoto = !empty($user->avatar_path);
        $avatarVersion = optional($user->updated_at)->timestamp ?? time();
    ?>

    <div class="u-grid-2">
    <div class="u-profile-card">
        <div class="u-profile-top">
            <?php if($hasPhoto): ?>
                <img src="<?php echo e(asset('storage/' . $user->avatar_path)); ?>?v=<?php echo e($avatarVersion); ?>"
                     alt="Profile photo"
                     class="u-avatar-lg"
                     id="avatarPreviewImg"
                     style="object-fit:cover;">
            <?php else: ?>
                <div class="u-avatar-lg" id="avatarPreviewFallback" style="--u-avatar-a:<?php echo e($avatarA); ?>;--u-avatar-b:<?php echo e($avatarB); ?>;">
                    <?php echo e(strtoupper(substr($user->name ?? 'G', 0, 1))); ?>

                </div>
            <?php endif; ?>
            <div>
                <h4>Hello, <?php echo e($user->name ?? 'Guest'); ?> 👋</h4>
                <span class="u-role-pill">Customer Account</span>
            </div>
        </div>

        <p class="u-field-label">Display name</p>

        <form method="POST" action="<?php echo e(route('user.account.update')); ?>" enctype="multipart/form-data" class="u-name-row" id="accountForm">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="avatar_theme" value="<?php echo e($currentAvatar); ?>" id="avatarThemeField">
            <input type="text" name="name" class="u-input" value="<?php echo e(old('name', $user->name ?? '')); ?>" maxlength="60" required>
            <button type="submit" class="u-icon-btn save" title="Save changes">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"></path></svg>
            </button>
        </form>

        <p class="u-field-label">Profile photo</p>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
            <label for="avatarPhotoInput" class="u-btn ghost" style="width:auto;display:inline-block;padding:9px 16px;font-size:12px;cursor:pointer;margin:0;">
                <span id="avatarPhotoLabel">Change photo</span>
            </label>
            <input type="file" id="avatarPhotoInput" name="avatar_photo" accept="image/png,image/jpeg,image/webp"
                   form="accountForm" style="display:none;">
            <span style="font-size:11.5px;color:var(--muted);">JPG, PNG, or WEBP. Max 2MB.</span>
        </div>

        <p class="u-field-label">Avatar color</p>
        <p style="font-size:11.5px;color:var(--muted);margin:-4px 0 10px;">Used when you don't have a profile photo set.</p>
        <div class="u-swatches">
            <?php $__currentLoopData = $avatarChoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $hues): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <label class="u-swatch"
                       style="background:linear-gradient(135deg,<?php echo e($hues[0]); ?>,<?php echo e($hues[1]); ?>);"
                       title="<?php echo e(ucfirst($key)); ?>">
                    <input type="radio" name="avatar_theme_picker" value="<?php echo e($key); ?>"
                           <?php echo e($currentAvatar === $key ? 'checked' : ''); ?>

                           onclick="document.getElementById('avatarThemeField').value=this.value;document.getElementById('accountForm').submit();">
                </label>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <div>
        <div class="u-card">
            <h4>Email</h4>
            <p><?php echo e($user->email ?? 'guest@example.com'); ?></p>
        </div>

        <div class="u-card" style="border-left-color:var(--teal);background:var(--teal-pale);">
            <h4>Account status</h4>
            <p>Your account is active and in good standing. Signature and booking history are tied to this profile.</p>
        </div>
    </div>
    </div>

    <form method="POST" action="<?php echo e(route('logout')); ?>" class="only-mobile">
        <?php echo csrf_field(); ?>
        <button type="submit" class="u-btn ghost">Logout</button>
    </form>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // Instant client-side preview before the form submits
    document.getElementById('avatarPhotoInput').addEventListener('change', function (e) {
        var file = e.target.files[0];
        if (!file) return;

        document.getElementById('avatarPhotoLabel').textContent = 'Uploading...';

        var reader = new FileReader();
        reader.onload = function (evt) {
            var existingImg = document.getElementById('avatarPreviewImg');
            var fallback = document.getElementById('avatarPreviewFallback');

            if (existingImg) {
                existingImg.src = evt.target.result;
            } else if (fallback) {
                var img = document.createElement('img');
                img.src = evt.target.result;
                img.id = 'avatarPreviewImg';
                img.alt = 'Profile photo';
                img.className = 'u-avatar-lg';
                img.style.objectFit = 'cover';
                fallback.replaceWith(img);
            }

            document.getElementById('accountForm').submit();
        };
        reader.readAsDataURL(file);
    });

   
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\REKS\REKS\laravel-app\resources\views/user/dashboard.blade.php ENDPATH**/ ?>