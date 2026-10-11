<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Route;

/**
 * Helper class for environment-aware admin routing.
 */
class AdminRouteHelper
{
    /**
     * Get the environment-aware route name for admin routes.
     *
     * @param string $routeName The route name without the admin prefix
     * @return string The full route name with appropriate prefix
     */
    public static function route(string $routeName): string
    {
        // Strip leading admin. if provided
        if (str_starts_with($routeName, 'admin.')) {
            $routeName = substr($routeName, 6);
        }

        $httpHost = request() ? request()->getHost() : '';

        // If request is explicitly on an admin subdomain:
        if (str_starts_with($httpHost, 'admin.')) {
            $env = config('app.env');

            if ($env === 'local') {
                if ($httpHost === 'admin.localhost' || str_contains($httpHost, 'admin.localhost')) {
                    return 'admin.local.'.$routeName;
                } elseif ($httpHost === 'admin.'.config('app.base_domain') || str_contains($httpHost, 'admin.'.config('app.base_domain'))) {
                    return 'admin.test.'.$routeName;
                }
                return 'admin.local.'.$routeName;
            }

            return 'admin.'.$routeName;
        }

        // On single-domain deployments (e.g. Render: eclore-backend.onrender.com/admin/...)
        // or local access via /admin prefix:
        return 'admin.path.'.$routeName;
    }

    /**
     * Generate a URL for an admin route.
     *
     * @param string $routeName The route name without the admin prefix
     * @param array|object $parameters Route parameters (array or model objects)
     * @return string The generated URL
     */
    public static function url(string $routeName, $parameters = []): string
    {
        $url = route(self::route($routeName), $parameters);

        $httpHost = request() ? request()->getHost() : '';
        $currentPort = request() ? request()->getPort() : null;
        $currentScheme = request() ? request()->getScheme() : 'https';

        if (str_starts_with($httpHost, 'admin.')) {
            $parsedUrl = parse_url($url);
            $path = $parsedUrl['path'] ?? '/';
            $query = isset($parsedUrl['query']) ? '?'.$parsedUrl['query'] : '';
            $fragment = isset($parsedUrl['fragment']) ? '#'.$parsedUrl['fragment'] : '';

            $port = ($currentPort && $currentPort != 80 && $currentPort != 443) ? ':'.$currentPort : '';
            $scheme = ($currentPort == 8443) ? 'https' : $currentScheme;

            return $scheme.'://'.$httpHost.$port.$path.$query.$fragment;
        }

        // Non-subdomain access (e.g. eclore-backend.onrender.com/admin/...):
        // route('admin.path.' . $routeName) already generated the correct URL
        return $url;
    }

    /**
     * Rebuild a URL with the correct domain based on current environment.
     * Useful for fixing stored URLs that need to be updated for the current environment.
     *
     * @param string $url The URL to rebuild
     * @return string The rebuilt URL with correct domain
     */
    public static function rebuildUrl(string $url): string
    {
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'] ?? '/';
        $query = isset($parsedUrl['query']) ? '?'.$parsedUrl['query'] : '';
        $fragment = isset($parsedUrl['fragment']) ? '#'.$parsedUrl['fragment'] : '';

        $currentHost = request() ? request()->getHost() : '';
        $currentPort = request() ? request()->getPort() : null;
        $currentScheme = request() ? request()->getScheme() : 'https';

        if (str_starts_with($currentHost, 'admin.')) {
            $port = ($currentPort && $currentPort != 80 && $currentPort != 443) ? ':'.$currentPort : '';
            $scheme = ($currentPort == 8443) ? 'https' : $currentScheme;

            return $scheme.'://'.$currentHost.$port.$path.$query.$fragment;
        }

        return $url;
    }

    /**
     * Generate an environment-aware URL for frontend routes.
     * Handles localhost port variations and production domain.
     *
     * @param string $routeName The route name
     * @param array|object $parameters Route parameters
     * @return string The generated URL
     */
    public static function frontendUrl(string $routeName, $parameters = []): string
    {
        $url = route($routeName, $parameters);

        $currentHost = request() ? request()->getHost() : '';
        $baseDomain = config('app.base_domain');

        // Only adjust domain if currently on an admin subdomain
        if (str_starts_with($currentHost, 'admin.') && $baseDomain) {
            $parsedUrl = parse_url($url);
            $path = $parsedUrl['path'] ?? '/';
            $query = isset($parsedUrl['query']) ? '?'.$parsedUrl['query'] : '';
            $fragment = isset($parsedUrl['fragment']) ? '#'.$parsedUrl['fragment'] : '';

            $env = config('app.env');
            if ($env === 'local') {
                $currentPort = request() ? request()->getPort() : null;
                $currentScheme = request() ? request()->getScheme() : 'http';
                $port = ($currentPort && $currentPort != 80 && $currentPort != 443) ? ':'.$currentPort : '';
                return $currentScheme.'://'.$baseDomain.$port.$path.$query.$fragment;
            }

            $frontendUrl = config('app.frontend_url') ?: 'https://'.$baseDomain;
            return $frontendUrl.$path.$query.$fragment;
        }

        return $url;
    }
}

/*
 * Global helper function for admin routes
 *
 * @param  string  $routeName  The route name without the admin prefix
 * @param  array|object  $parameters  Route parameters (array or model objects)
 * @return string The generated URL
 */
if (! function_exists('admin_route')) {
    function admin_route(string $routeName, $parameters = []): string
    {
        return \App\Helpers\AdminRouteHelper::url($routeName, $parameters);
    }
}

/*
 * Global helper function for frontend routes with environment-aware URLs
 *
 * @param  string  $routeName  The route name
 * @param  array|object  $parameters  Route parameters (array or model objects)
 * @return string The generated URL
 */
if (! function_exists('frontend_route')) {
    function frontend_route(string $routeName, $parameters = []): string
    {
        return \App\Helpers\AdminRouteHelper::frontendUrl($routeName, $parameters);
    }
}
