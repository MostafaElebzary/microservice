<?php

namespace App\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Exception;
use Illuminate\Support\Facades\Log;

class RabbitMQService
{
    protected $connection;
    protected $channel;

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
     * Start consuming token validation requests
     */
    public function consume(string $queue, callable $handler): void
    {
        $this->channel->queue_declare($queue, false, true, false, false);

        $callback = function (AMQPMessage $msg) use ($handler) {
            try {
                // Pass the full message object to handler
                $handler($msg);
            } catch (Exception $e) {
                Log::error("Message processing failed: " . $e->getMessage());
                $msg->nack(); // Negative acknowledgment
            }
        };

        $this->channel->basic_consume(
            $queue,
            '',
            false,
            false,
            false,
            false,
            $callback
        );

        while ($this->channel->is_consuming()) {
            $this->channel->wait();
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
