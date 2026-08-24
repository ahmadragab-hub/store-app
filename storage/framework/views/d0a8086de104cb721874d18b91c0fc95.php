<?php $__env->startSection('title', 'Log in'); ?>

<?php $__env->startSection('content'); ?>
    <section class="w-full max-w-md">
        <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
            <p class="text-sm font-medium text-accent">Welcome back</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-stone-900">Log in to your account</h1>
            <p class="mt-2 text-sm text-stone-600">Use the same email you registered with.</p>

            <form method="POST" action="<?php echo e(route('login')); ?>" class="mt-8 space-y-5">
                <?php echo csrf_field(); ?>

                <div>
                    <label for="email" class="store-label">Email</label>
                    <input id="email" name="email" type="email" value="<?php echo e(old('email')); ?>" required autofocus autocomplete="email" class="store-input">
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="store-error"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div>
                    <label for="password" class="store-label">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password" class="store-input">
                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="store-error"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <button type="submit" class="store-button">Log in</button>
            </form>
        </div>

        <p class="mt-6 text-center text-sm text-stone-600">
            Need an account?
            <a href="<?php echo e(route('register')); ?>" class="font-semibold text-stone-900 underline decoration-stone-300 underline-offset-4 hover:decoration-stone-900">Register</a>
        </p>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\wamp64\www\store-app\resources\views/auth/login.blade.php ENDPATH**/ ?>