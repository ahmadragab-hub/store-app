<?php

namespace App\Http\Controllers;

use App\Services\StripeWebhookService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request, StripeWebhookService $webhooks): Response
    {
        $webhooks->handle(
            $request->getContent(),
            $request->header('Stripe-Signature'),
        );

        return response()->noContent();
    }
}
