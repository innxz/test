<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\ProcessEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\QueueManager;

final class FetchMessagesCommand extends Command
{
    protected $signature = 'app:fetch-messages';

    public function handle(
        QueueManager $queueManager,
    ): void
    {
        try {
            $this->info('Начинаю обработку файла');

            $file = 'events.json';

            if (!Storage::fileExists($file)) {
                $this->error('Файл с данныит не существует или не найден');

                return;
            }

            $data = json_decode(Storage::get($file), true);

            foreach ($data as $item) {
                $key = 'event_user_queue__' . $item['user_id'];

                if (Cache::has($key)) {
                    $queue = Cache::get($key);
                } else {
                    $queue = $this->getOptimalQueue($queueManager);
                }

                ProcessEvent::dispatch($item['user_id'], $item['event_id'])->onQueue($queue);
                Cache::set($key, $queue, 3600);
            }

            $this->info('Файл успешно обработан');
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    private function getOptimalQueue(QueueManager $queueManager): string
    {
        $queuesSizes = [];

        foreach (config('queue.users_events') as $queue) {
            $count = $queueManager->size($queue);

            if ($count === 0) {
                return $queue;
            }

            $queuesSizes[$queue] = $count;
        }

        return array_keys($queuesSizes, min($queuesSizes))[0];
    }
}
