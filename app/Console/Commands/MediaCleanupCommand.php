<?php

namespace App\Console\Commands;

use App\Models\Image;
use App\Settings\SettingsManager;
use Illuminate\Console\Command;

class MediaCleanupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes all the media resources that have expired.';

    /**
     * The application settings.
     *
     * @var \App\Settings\SettingsManager
     */
    protected $settings;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SettingsManager $settings)
    {
        parent::__construct();

        $this->settings = $settings;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->cleanupImages();
    }

    /**
     * Cleans up the expired images.
     *
     * @return void
     */
    protected function cleanupImages()
    {
        $images = Image::where('created_at', '<', $this->createTimestampFor('images'))->get();

        if ($images->isEmpty()) {
            return $this->warn('No images to cleanup, skipping...');
        }

        $this->info('Starting cleanup process for ' . $images->count() . ' images!');

        foreach ($images as $image) {
            $image->delete();
        }

        $this->info('Done!');
    }

    /**
     * Creates a Carbon timestamp using the given settings type.
     *
     * @param  string $type
     * @return \Carbon\Carbon
     */
    protected function createTimestampFor($type)
    {
        return now()
            ->subDays($this->settings->get($type . '.ttl_days'))
            ->subHours($this->settings->get($type . '.ttl_hours'))
            ->subMinutes($this->settings->get($type . '.ttl_minutes'));
    }
}
