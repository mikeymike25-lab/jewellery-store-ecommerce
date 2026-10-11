<?php

/**
 * Global helper functions for the application.
 */
if (! function_exists('admin_route')) {
    /**
     * Generate a URL for an admin route.
     *
     * @param string $routeName The route name without the admin prefix
     * @param array|object $parameters Route parameters (array or model objects)
     * @return string The generated URL
     */
    function admin_route(string $routeName, $parameters = []): string
    {
        // Use AdminRouteHelper for environment and host-aware route generation
        if (class_exists(\App\Helpers\AdminRouteHelper::class)) {
            return \App\Helpers\AdminRouteHelper::url($routeName, $parameters);
        }

        if (class_exists(\App\Helpers\RouteHelper::class)) {
            return \App\Helpers\RouteHelper::adminRoute($routeName, $parameters);
        }

        // Fallback: Use standard route helper
        $env = config('app.env');
        $prefix = $env === 'local' ? 'admin.test.' : 'admin.';

        return route($prefix.$routeName, $parameters);
    }
}

if (! function_exists('frontend_route')) {
    /**
     * Generate an environment-aware URL for frontend routes.
     *
     * @param string $routeName The route name
     * @param array|object $parameters Route parameters (array or model objects)
     * @return string The generated URL
     */
    function frontend_route(string $routeName, $parameters = []): string
    {
        // Use AdminRouteHelper if available
        if (class_exists(\App\Helpers\AdminRouteHelper::class)) {
            return \App\Helpers\AdminRouteHelper::frontendUrl($routeName, $parameters);
        }

        // Fallback: Use standard route helper
        return route($routeName, $parameters);
    }
}

if (! function_exists('storage_disk')) {
    /**
     * Get the appropriate storage disk based on environment
     * Uses S3 if configured, otherwise falls back safely to public.
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    function storage_disk()
    {
        // Only use S3 if S3 disk is default or production AND bucket is explicitly configured
        $s3Bucket = config('filesystems.disks.s3.bucket');
        if (! empty($s3Bucket) && (config('filesystems.default') === 's3' || config('app.env') === 'production')) {
            try {
                return \Illuminate\Support\Facades\Storage::disk('s3');
            } catch (\Throwable $e) {
                // S3 unavailable or credentials missing, fall back to public
            }
        }

        try {
            $disk = config('filesystems.default');
            if ($disk && $disk !== 'local' && $disk !== 'public' && $disk !== 's3') {
                return \Illuminate\Support\Facades\Storage::disk($disk);
            }
        } catch (\Throwable $e) {
            // Log and fallback
        }

        return \Illuminate\Support\Facades\Storage::disk('public');
    }
}

if (! function_exists('storage_url')) {
    /**
     * Get the appropriate storage URL based on environment
     * Uses local URLs for localhost, S3 URLs when configured.
     */
    function storage_url(?string $path): string
    {
        if (! $path) {
            return '';
        }

        // If it's already a full URL, just return it
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        // Static frontend and asset files belong to public directory directly
        if (str_starts_with($path, '/assets/') || str_starts_with($path, 'assets/') ||
            str_starts_with($path, '/frontend/') || str_starts_with($path, 'frontend/') ||
            str_starts_with($path, '/images/') || str_starts_with($path, 'images/')) {
            return asset(ltrim($path, '/'));
        }

        try {
            // Use the centralized dynamic URL generation logic from the Storage macro if available
            if (\Illuminate\Support\Facades\Storage::hasMacro('getDynamicUrl')) {
                return \Illuminate\Support\Facades\Storage::getDynamicUrl($path);
            }

            // Fallback: Use the dynamic disk's URL generator directly
            $disk = storage_disk();

            return $disk->url($path);
        } catch (\Throwable $e) {
            // Silence errors and allow fallback
        }

        // Final fallback: construct local path if all else fails
        return asset('storage/'.ltrim($path, '/'));
    }
}

