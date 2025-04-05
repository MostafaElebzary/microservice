<?php

namespace App\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Exception;
use Illuminate\Support\Facades\Log;

class RabbitMQService
{
    private $connection;
    private $channel;
    private $response;
    private $correlationId;

    public function __construct()
    {
        $this->connect();
    }

    protected function connect(): void
    {
        try {
            $this->connection = new AMQPStreamConnection(
                env('RABBITMQ_HOST', 'rabbitmq'),
                env('RABBITMQ_PORT', 5672),
                env('RABBITMQ_USER', 'guest'),
                env('RABBITMQ_PASS', 'guest')
            );
            $this->channel = $this->connection->channel();
        } catch (Exception $e) {
            Log::error("RabbitMQ Connection Failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Make RPC request with timeout
     */
    public function rpcRequest(string $queue, array $payload, int $timeout = 5): array
    {
        $this->correlationId = uniqid();
        $this->response = null;

        // Create temporary callback queue
        list($callbackQueue) = $this->channel->queue_declare('', false, false, true, true);

        // Prepare consumer
        $this->channel->basic_consume(
            $callbackQueue,
            '',
            false,
            true,
            false,
            false,
            [$this, 'handleResponse']
        );

        // Publish request
        $this->channel->basic_publish(
            new AMQPMessage(
                json_encode($payload),
                [
                    'correlation_id' => $this->correlationId,
                    'reply_to' => $callbackQueue,
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
                ]
            ),
            '',
            $queue
        );

        // Wait for response
        $start = microtime(true);
        while (is_null($this->response)) {
            if ((microtime(true) - $start) > $timeout) {
                throw new Exception("RPC timeout after {$timeout} seconds");
            }
            $this->channel->wait(null, false, $timeout);
        }

        return $this->response;
    }

    /**
     * Handle RPC response
     */
    public function handleResponse(AMQPMessage $msg): void
    {
        if ($msg->get('correlation_id') === $this->correlationId) {
            $this->response = json_decode($msg->body, true);
        }
    }

    public function __destruct()
    {
        try {
            $this->channel?->close();
            $this->connection?->close();
        } catch (Exception $e) {
            Log::error("RabbitMQ cleanup error: " . $e->getMessage());
        }
    }
}
