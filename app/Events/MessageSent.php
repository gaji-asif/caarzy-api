<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // Instant broadcast
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        // Log channel creation
        Log::info('📡 Broadcasting message', [
            'receiver_channel' => 'user.' . $this->message->receiver_id,
            'sender_channel'   => 'user.' . $this->message->sender_id,
        ]);

        // Broadcast to both sender and receiver private channels
        return [
            new PrivateChannel('user.' . $this->message->receiver_id),
            new PrivateChannel('user.' . $this->message->sender_id),
        ];
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->message->id,
            'sender_id' => $this->message->sender_id,
            'receiver_id' => $this->message->receiver_id,
            'message_body' => $this->message->message_body,
            'created_at' => optional($this->message->created_at)->toISOString(),
            'sender' => [
                'id' => $this->message->sender->id,
                'name' => $this->message->sender->name,
            ],
        ];
    }

    public function broadcastAs()
    {
        // Optional: customize event name (frontend listens to this)
        return 'MessageSent';
    }
}
