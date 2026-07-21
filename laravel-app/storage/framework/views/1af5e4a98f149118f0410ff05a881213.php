<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', 'Waiver'); ?> · WonderPark Amusement</title>
    <link rel="stylesheet" href="<?php echo e(asset('css/user-app-theme.css')); ?>">

    <style>
        body{
            margin:0;
            min-height:100vh;
            display:flex;
            align-items:flex-start;
            justify-content:center;
            background:linear-gradient(180deg,#FAF6F9 0%,#F3ECF1 100%);
            font-family:inherit;
            padding:32px 16px;
        }
        .waiver-shell{
            width:100%;
            max-width:640px;
        }
        .waiver-brand{
            text-align:center;
            margin-bottom:20px;
        }
        .waiver-brand img{
            height:48px;
            margin-bottom:8px;
        }
        .waiver-brand h1{
            font-size:1.1rem;
            margin:0;
            color:var(--ink);
            letter-spacing:.02em;
        }
        .waiver-brand p{
            margin:2px 0 0;
            font-size:.85rem;
            color:var(--ink-soft);
        }
        .waiver-shell .u-card,
        .waiver-shell .waiver-box{
            box-shadow:0 8px 28px rgba(20,10,25,.06);
        }
        .waiver-shell form{
            margin-top:16px;
        }
    </style>

    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>
    <div class="waiver-shell">
        <div class="waiver-brand">
            <h1>WonderPark Amusement</h1>
            <p>Please review and accept before continuing</p>
        </div>

        <?php if(session('success')): ?>
            <div class="u-alert success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="u-alert error"><?php echo e(session('error')); ?></div>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <div class="u-alert error"><?php echo e($errors->first()); ?></div>
        <?php endif; ?>

        <?php echo $__env->yieldContent('content'); ?>
    </div>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html><?php /**PATH C:\xampp\htdocs\WONDERPARK\WONDERPARK2\laravel-app\resources\views/layouts/waiver-only.blade.php ENDPATH**/ ?>