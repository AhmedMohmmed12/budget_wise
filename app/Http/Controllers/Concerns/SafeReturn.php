<?php

namespace App\Http\Controllers\Concerns;

trait SafeReturn
{
    /**
     * Only follow "return" links that point back into this app.
     */
    protected function safeReturn(mixed $url): string
    {
        return is_string($url) && str_starts_with($url, url('/').'/') ? $url : route('dashboard');
    }
}
