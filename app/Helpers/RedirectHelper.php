<?php

namespace App\Helpers;

use Illuminate\Support\Facades\URL;

class RedirectHelper
{
    /**
     * Validate if a URL is safe for redirect (only internal URLs allowed)
     * 
     * @param string|null $url
     * @return string|null Safe URL or null if unsafe
     */
    public static function validateRedirect(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        // If it's already a route name, it's safe
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        // Parse the URL
        $parsedUrl = parse_url($url);
        
        // Get current app URL
        $appUrl = parse_url(config('app.url'));
        $appHost = $appUrl['host'] ?? null;
        $appScheme = $appUrl['scheme'] ?? 'http';

        // Only allow redirects to same host
        if (isset($parsedUrl['host']) && $parsedUrl['host'] !== $appHost) {
            return null; // External URL, reject
        }

        // Only allow http/https schemes
        if (isset($parsedUrl['scheme']) && !in_array($parsedUrl['scheme'], ['http', 'https'])) {
            return null; // Dangerous scheme (javascript:, data:, etc.)
        }

        // Reject javascript: and data: URLs
        if (stripos($url, 'javascript:') === 0 || stripos($url, 'data:') === 0) {
            return null;
        }

        return $url;
    }

    /**
     * Get safe intended URL or fallback to default route
     * 
     * @param \Illuminate\Http\Request $request
     * @param string $defaultRoute
     * @return string
     */
    public static function safeIntended($request, string $defaultRoute): string
    {
        // Get intended URL from session (Laravel stores it in 'url.intended')
        $intended = $request->session()->get('url.intended');
        
        if ($intended) {
            $safeUrl = self::validateRedirect($intended);
            if ($safeUrl) {
                $request->session()->forget('url.intended');
                return $safeUrl;
            }
            // If URL is unsafe, remove it from session
            $request->session()->forget('url.intended');
        }
        
        return route($defaultRoute);
    }
}
