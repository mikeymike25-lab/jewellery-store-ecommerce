<?php

namespace App\Providers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

class StorageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Override the storage URL to be dynamic based on current request
        Storage::macro('getDynamicUrl', function ($path) {
            $disk = Storage::getDynamicDisk();

            // If using S3, let the S3 driver handle URL generation
            if ($disk === 's3') {
                try {
                    return Storage::disk('s3')->url($path);
                } catch (\Throwable $e) {
                    // Fall back to local URL generation
                }
            }

            // Fallback to local URL generation
            $scheme = request()->getScheme();
            $host = request()->getHost();
            $port = request()->getPort();

            // Don't include port if it's the default for the scheme
            $portString = ($scheme === 'https' && $port === 443) || ($scheme === 'http' && $port === 80) ? '' : ':'.$port;

            return $scheme.'://'.$host.$portString.'/storage/'.ltrim($path, '/');
        });

        // Add macro for dynamic storage disk selection
        Storage::macro('getDynamicDisk', function () {
            $env = config('app.env');
            $host = request()->getHost();
            $s3Bucket = config('filesystems.disks.s3.bucket');

            // Use S3 only if explicitly configured with a non-empty bucket
            if (! empty($s3Bucket) && (config('filesystems.default') === 's3' || $env === 'production')) {
                return 's3';
            }

            // Use local storage for localhost development
            if (str_contains($host, 'localhost') || str_contains($host, '127.0.0.1') || str_contains($host, '.test')) {
                return 'public';
            }

            // Default to public for other environments
            return 'public';
        });

        // Add macro for dynamic storage operations
        Storage::macro('dynamic', function () {
            $disk = Storage::getDynamicDisk();

            return Storage::disk($disk);
        });
    }
}
