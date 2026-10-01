<?php

namespace App\Providers;

use App\Contracts\PaymentGateway;
use App\Services\MockPaymentGateway;
use App\Services\StripePaymentGateway;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Services\GuestCartService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(StripePaymentGateway::class, function () {
            return new StripePaymentGateway(config('payments.stripe.secret'));
        });

        $this->app->singleton(PaymentGateway::class, function ($app) {
            $driver = (string) config('payments.driver', 'mock');

            return match ($driver) {
                'stripe' => $app->make(StripePaymentGateway::class),
                default => new MockPaymentGateway(),
            };
        });
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        URL::forceHttps($this->app->isProduction());

        if ($this->app->isProduction()) {
            $this->app['config']->set('session.secure', true);
            $this->app['config']->set('session.encrypt', true);
        }

        View::composer('layouts.app', function ($view) {
            $count = 0;

            if (auth()->check()) {
                $cart = auth()->user()->cart;
                $count = $cart?->items()->sum('quantity') ?? 0;
            } else {
                $count = app(GuestCartService::class)->sum();
            }

            $view->with('cartItemCount', (int) $count);
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip());
        });

        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perMinute(3)->by(strtolower((string) $request->input('email')).'|'.$request->ip());
        });
    }
}
