<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

final class GenerateEventsFileCommand extends Command
{
    protected $signature = 'app:generate-events-file';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        try {
            $this->info('Начинаю генерация файла');

            $file = 'events.json';

            if (Storage::fileExists($file)) {
                $this->info('Удаляю предыдущий файл');
                Storage::delete($file);
            }

            $data = [];

            for ($i = 1; $i <= 10000; $i++) {
                $data[] = $this->generateUserEvent($data);
            }

            Storage::put('events.json', json_encode($data));

            $this->info('Файл успешно сгенерирован');
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());
        }
    }

    private function generateUserEvent(array $usersEvents): array
    {
        $userId = rand(1, 1000);

        $lastRecord = null;

        foreach (array_reverse($usersEvents) as $userEvent) {
            if ($userEvent['user_id'] === $userId) {
                $lastRecord = $userEvent;

                break;
            }
        }

        $eventId = $lastRecord ? $lastRecord['event_id'] + 1 : 1;

        return [
            'user_id' => $userId,
            'event_id' => $eventId,
        ];
    }
}
