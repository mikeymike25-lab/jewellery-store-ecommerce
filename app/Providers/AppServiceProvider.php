<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Dynamically set APP_URL based on the request domain
        $host = request()->getHost();
        $scheme = request()->getScheme();
        $port = request()->getPort();
        $appEnv = config('app.env');
        $baseDomain = config('app.base_domain');
        $adminUrl = config('app.admin_url');
        $frontendUrl = config('app.frontend_url');

        // Construct port string if not standard
        $portString = (($scheme === 'https' && $port === 443) || ($scheme === 'http' && $port === 80)) ? '' : ':'.$port;

        // Production domains
        if ($appEnv === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');

            if ($adminUrl && (str_contains($host, 'admin.') || $host === parse_url($adminUrl, PHP_URL_HOST))) {
                config(['app.url' => $adminUrl]);
            } elseif ($frontendUrl && ($host === parse_url($frontendUrl, PHP_URL_HOST))) {
                config(['app.url' => $frontendUrl]);
            } else {
                // Fallback: construct URL from current request in production (force https)
                config(['app.url' => 'https://'.$host.$portString]);
            }
        }
        // Local development domains
        elseif ($appEnv === 'local') {
            if (str_contains($host, 'admin.')) {
                // For admin subdomains, preserve the current host and port
                config(['app.url' => $scheme.'://'.$host.$portString]);
            } else {
                // For main domain, preserve the current host and port
                config(['app.url' => $scheme.'://'.$host.$portString]);
            }
        }
        // For other environments, use env() or construct from request
        else {
            $envUrl = env('APP_URL');
            if (! $envUrl) {
                $portString = (($scheme === 'https' && $port === 443) || ($scheme === 'http' && $port === 80)) ? '' : ':'.$port;
                config(['app.url' => $scheme.'://'.$host.$portString]);
            }
        }

        // Register Blade directive for admin routes
        Blade::directive('adminRoute', function ($routeName) {
            return "<?php echo \\App\\Helpers\\AdminRouteHelper::route($routeName); ?>";
        });
    }
}
