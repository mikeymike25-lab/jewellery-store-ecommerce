<?php

namespace App\Helpers;

class RouteHelper
{
    /**
     * Get the correct admin route name based on environment.
     */
    public static function adminRoute(string $routeName, $parameters = []): string
    {
        if (class_exists(\App\Helpers\AdminRouteHelper::class)) {
            return \App\Helpers\AdminRouteHelper::url($routeName, $parameters);
        }

        $env = config('app.env');
        $httpHost = request() ? request()->server->get('HTTP_HOST') : '';
        $host = request() ? request()->getHost() : '';
        $port = request() ? request()->getPort() : null;
        $scheme = request() ? request()->getScheme() : 'https';

        $baseDomain = config('app.base_domain');

        if (str_starts_with($host, 'admin.')) {
            if ($env === 'local') {
                if (str_contains($httpHost, 'admin.'.$baseDomain)) {
                    $prefix = 'admin.test.';
                } elseif (str_contains($httpHost, 'admin.localhost')) {
                    $prefix = 'admin.local.';
                } else {
                    $prefix = 'admin.test.';
                }
            } else {
                $prefix = 'admin.';
            }
        } else {
            $prefix = 'admin.path.';
        }

        $url = route($prefix.$routeName, $parameters);

        if ($env === 'production' || (request() && request()->header('x-forwarded-proto') === 'https')) {
            $url = preg_replace('/^http:\/\//', 'https://', $url);
        }

        return $url;
    }

    /**
     * Get the correct route name for any route based on environment.
     */
    public static function getRoute(string $routeName): string
    {
        if (class_exists(\App\Helpers\AdminRouteHelper::class) && str_starts_with($routeName, 'admin.')) {
            return \App\Helpers\AdminRouteHelper::route($routeName);
        }

        return $routeName;
    }
}
