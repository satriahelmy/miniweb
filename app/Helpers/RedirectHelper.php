<?php

namespace App\Helpers;

use Illuminate\Support\Facades\URL;

class RedirectHelper
{
    public static function validateRedirect(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        $parsedUrl = parse_url($url);
        $appUrl = parse_url(config('app.url'));
        $appHost = $appUrl['host'] ?? null;

        if (isset($parsedUrl['host']) && $parsedUrl['host'] !== $appHost) {
            return null;
        }

        if (isset($parsedUrl['scheme']) && !in_array($parsedUrl['scheme'], ['http', 'https'])) {
            return null;
        }

        if (stripos($url, 'javascript:') === 0 || stripos($url, 'data:') === 0) {
            return null;
        }

        return $url;
    }

    public static function safeIntended($request, string $defaultRoute): string
    {
        $intended = $request->session()->get('url.intended');
        
        if ($intended) {
            $safeUrl = self::validateRedirect($intended);
            if ($safeUrl) {
                $request->session()->forget('url.intended');
                return $safeUrl;
            }
            $request->session()->forget('url.intended');
        }
        
        return route($defaultRoute);
    }
}
