<?php
// app/Http/Middleware/SetLocaleFromHeader.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocaleFromHeader
{

    private array $availableLocales = ['uk', 'en'];


    private string $defaultLocale = 'uk';

    public function handle(Request $request, Closure $next)
    {
        $locale = $request->header('Accept-Language', $this->defaultLocale);

        $locale = strtolower(substr($locale, 0, 2));

        if (!in_array($locale, $this->availableLocales)) {
            $locale = $this->defaultLocale;
        }

        App::setLocale($locale);

        return $next($request);
    }
}
