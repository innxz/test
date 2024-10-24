<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\UserEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Psr\Log\LoggerInterface;

final class ProcessEvent implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly int $userId,
        private readonly int $evenId,
    )
    {
    }

    /**
     * Execute the job.
     */
    public function handle(
        LoggerInterface $logger,
    ): void
    {
        try {
            sleep(1);

            if (!$this->hasUserEventsInQueue()) {
                Cache::delete('event_user_queue__' . $this->userId);
            }

            $userEvent = new UserEvent();
            $userEvent->user_id = $this->userId;
            $userEvent->event_id = $this->evenId;
            $userEvent->save();
        } catch (\Throwable $exception) {
            $this->fail($exception);
            $logger->error($exception->getMessage());
        }
    }

    public function hasUserEventsInQueue(): bool
    {
        $jobs = Redis::lrange('queues:' . $this->queue, 0, -1);

        foreach ($jobs as $job) {
            $data = json_decode($job, true);

            if (isset($data['data']['userId']) && $data['data']['userId'] == $this->userId) {
                return false;
            }
        }

        return false;
    }
}
