<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $__env->yieldContent('title', config('app.name')); ?></title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="min-h-screen bg-paper font-sans text-ink antialiased">
    <header class="border-b border-stone-200/80 bg-white/80 backdrop-blur">
        <nav class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
            <a href="<?php echo e(url('/')); ?>" class="text-lg font-semibold tracking-tight text-stone-900">
                <?php echo e(config('app.name')); ?>

            </a>

            <div class="flex items-center gap-3 text-sm">
                <?php if(auth()->guard()->check()): ?>
                    <span class="hidden text-stone-600 sm:inline"><?php echo e(auth()->user()->name); ?></span>
                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="rounded-lg px-3 py-1.5 font-medium text-stone-700 transition hover:bg-stone-100">
                            Log out
                        </button>
                    </form>
                <?php else: ?>
                    <a href="<?php echo e(route('login')); ?>" class="rounded-lg px-3 py-1.5 font-medium text-stone-700 transition hover:bg-stone-100">
                        Log in
                    </a>
                    <a href="<?php echo e(route('register')); ?>" class="rounded-lg bg-stone-900 px-3 py-1.5 font-semibold text-white transition hover:bg-stone-800">
                        Register
                    </a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main class="mx-auto flex w-full max-w-5xl flex-1 justify-center px-4 py-12 sm:px-6 sm:py-16">
        <?php echo $__env->yieldContent('content'); ?>
    </main>
</body>
</html>
<?php /**PATH C:\wamp64\www\store-app\resources\views/layouts/app.blade.php ENDPATH**/ ?>