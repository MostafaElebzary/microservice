<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;

class ValidateTokenJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public array $data)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if (!isset($this->data['token'])) {
                throw new Exception("Missing token in message");
            }

            // Validate Sanctum token
            $accessToken = PersonalAccessToken::findToken($this->data['token']);
            $isValid = $accessToken && (($accessToken->expires_at === null) || now()->lt($accessToken->expires_at));

            // Log the result
            if ($isValid) {
                Log::info("Token is valid for user: {$accessToken->tokenable->email}");
            } else {
                Log::warning("Invalid token received: {$this->data['token']}");
            }
        } catch (Exception $e) {
            Log::error("Failed to process token validation: " . $e->getMessage());
        }

    }
}
