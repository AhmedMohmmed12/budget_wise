<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MinifyHtml
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only minify HTML responses
        if ($this->isHtmlResponse($response)) {
            $content = $response->getContent();
            $minified = $this->minify($content);
            $response->setContent($minified);
        }

        return $response;
    }

    /**
     * Check if the response is HTML
     */
    protected function isHtmlResponse(Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type');

        return $contentType && str_contains($contentType, 'text/html');
    }

    /**
     * Minify HTML content
     */
    protected function minify(string $html): string
    {
        // Preserve pre, textarea, and script tags
        $preserve = [];
        $html = preg_replace_callback(
            '#<(pre|textarea|script)[^>]*?>.*?</\1>#is',
            function ($matches) use (&$preserve) {
                $placeholder = '___PRESERVE_'.count($preserve).'___';
                $preserve[$placeholder] = $matches[0];

                return $placeholder;
            },
            $html
        );

        // Remove HTML comments (except IE conditionals)
        $html = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $html);

        // Remove whitespace between tags
        $html = preg_replace('/>\s+</', '><', $html);

        // Remove leading and trailing whitespace from lines
        $html = preg_replace('/^\s+|\s+$/m', '', $html);

        // Collapse multiple spaces into one
        $html = preg_replace('/\s\s+/', ' ', $html);

        // Restore preserved content
        foreach ($preserve as $placeholder => $content) {
            $html = str_replace($placeholder, $content, $html);
        }

        return trim($html);
    }
}
