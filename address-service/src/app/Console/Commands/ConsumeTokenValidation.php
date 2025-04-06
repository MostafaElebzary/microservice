<?php

namespace App\Console\Commands;

use App\Domains\Address\Models\Address;
use Exception;
use Illuminate\Console\Command;
use App\Services\RabbitMQService;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Message\AMQPMessage;
use Laravel\Sanctum\PersonalAccessToken;

class ConsumeTokenValidation extends Command
{
    protected $signature = 'consume:user-address';
    protected $description = 'Consume token validation requests';

    public function handle(RabbitMQService $rabbitmq)
    {
        $rabbitmq->consume('user_address_queue', function (AMQPMessage $msg) {
            try {
                // 1. Decode message body
                $body = json_decode($msg->body, true);

                if (!isset($body['id'])) {
                    throw new Exception("Missing id in message");
                }

                // 2. Validate Sanctum token
                $address = Address::where('user_id', $body['id'])->get();

                // 3. Prepare response
                $response = $address;

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
                Log::error("user Address error: " . $e->getMessage());

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
