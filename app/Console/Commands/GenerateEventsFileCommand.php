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
                $data[] = [
                    'user_id' => rand(1, 1000),
                    'event_id' => rand(1, 10),
                ];
            }

            Storage::put('events.json', json_encode($data));

            $this->info('Файл успешно сгенерирован');
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());
        }
    }
}
