<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use App\Services\RabbitMQService;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Message\AMQPMessage;
use Laravel\Sanctum\PersonalAccessToken;

class ConsumeTokenValidation extends Command
{
    protected $signature = 'consume:token-validation';
    protected $description = 'Consume token validation requests';

    public function handle(RabbitMQService $rabbitmq)
    {
        $rabbitmq->consume('token_validation_queue', function (AMQPMessage $msg) {
            try {
                // 1. Decode message body
                $body = json_decode($msg->body, true);

                if (!isset($body['token'])) {
                    throw new Exception("Missing token in message");
                }

                // 2. Validate Sanctum token
                $accessToken = PersonalAccessToken::findToken($body['token']);
                $isValid = ($accessToken->expires_at === null) ||
                    ($accessToken->expires_at && now()->lt($accessToken->expires_at));

                // 3. Prepare response
                $response = [
                    'valid' => $isValid,
                    'user' => $isValid ? [
                        'id' => $accessToken->tokenable->id,
                        'email' => $accessToken->tokenable->email,
                        'abilities' => $accessToken->abilities
                    ] : null,
                    'timestamp' => now()->toDateTimeString()
                ];

                // 4. Send response
                $replyMsg = new AMQPMessage(
                    json_encode($response),
                    [
                        'correlation_id' => $msg->get('correlation_id'),
                        'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
                    ]
                );

                $msg->delivery_info['channel']->basic_publish(
                    $replyMsg,
                    '',
                    $msg->get('reply_to')
                );

                $msg->ack();

            } catch (Exception $e) {
                Log::error("Token validation error: " . $e->getMessage());

                // Send error response
                $errorResponse = new AMQPMessage(
                    json_encode(['valid' => false, 'error' => $e->getMessage()]),
                    [
                        'correlation_id' => $msg->get('correlation_id'),
                        'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
                    ]
                );

                $msg->delivery_info['channel']->basic_publish(
                    $errorResponse,
                    '',
                    $msg->get('reply_to')
                );

                $msg->ack();
            }
        });
    }

    protected function sendResponse(AMQPMessage $msg, array $response): void
    {
        $reply = new AMQPMessage(
            json_encode($response),
            [
                'correlation_id' => $msg->get('correlation_id'),
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
            ]
        );

        $msg->delivery_info['channel']->basic_publish(
            $reply,
            '',
            $msg->get('reply_to')
        );

        $msg->ack();
    }

    protected function sendErrorResponse(AMQPMessage $msg, string $error): void
    {
        $this->sendResponse($msg, [
            'valid' => false,
            'error' => $error
        ]);
    }
}
