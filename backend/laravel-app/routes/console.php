<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Command\Command;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('db:production-init', function (): int {
    $connection = DB::connection();
    $driver = $connection->getDriverName();

    if ($driver !== 'mysql') {
        $this->error("Refusing to initialize production data on [{$driver}]. Set DB_CONNECTION=mysql and Railway MySQL variables first.");

        return Command::FAILURE;
    }

    $this->info('Running migrations...');
    $migrateCode = Artisan::call('migrate', [
        '--force' => true,
        '--no-interaction' => true,
    ]);

    $this->output->write(Artisan::output());

    if ($migrateCode !== Command::SUCCESS) {
        $this->error('Migrations failed. Seeding was not attempted.');

        return $migrateCode;
    }

    $seedTables = [
        'users',
        'movies',
        'genres',
        'movies_genres_usage',
        'halls',
        'seats',
        'screenings',
        'feedbacks',
    ];

    $existingCounts = collect($seedTables)
        ->filter(fn (string $table): bool => Schema::hasTable($table))
        ->mapWithKeys(fn (string $table): array => [$table => DB::table($table)->count()])
        ->filter(fn (int $count): bool => $count > 0);

    if ($existingCounts->isNotEmpty()) {
        $this->warn('Seed data was not run because production tables already contain data:');

        $existingCounts->each(function (int $count, string $table): void {
            $this->line("- {$table}: {$count}");
        });
        $this->line('Run php artisan db:seed --force manually only if you intentionally want to update demo seed rows.');

        return Command::SUCCESS;
    }

    $this->info('Production seed tables are empty. Running seeders...');
    $seedCode = Artisan::call('db:seed', [
        '--force' => true,
        '--no-interaction' => true,
    ]);

    $this->output->write(Artisan::output());

    return $seedCode;
})->purpose('Run production-safe migrations and seed the initial demo data only when seed tables are empty');
