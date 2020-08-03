<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Url;

use Magento\Framework\View\FileSystem;

/**
 * CSS URLs resolver class.
 * This utility class provides a set of methods to work with CSS files.
 * @api
 * @since 100.0.2
 */
class CssResolver
{
    /**
     * PCRE that matches non-absolute URLs in CSS content
     */
    const REGEX_CSS_RELATIVE_URLS =
        '#url\s*\(\s*(?(?=\'|").)(?!http\://|https\://|/|data\:)(.+?)(?:[\#\?].*?|[\'"])?\s*\)#';

    /**
     * Adjust relative URLs in CSS content as if the file with this content is to be moved to new location
     *
     * @param string $cssContent
     * @param string $relatedPath
     * @param string $filePath
     * @return mixed
     */
    public function relocateRelativeUrls($cssContent, $relatedPath, $filePath)
    {
        $offset = FileSystem::offsetPath($relatedPath, $filePath);
        $callback = function ($path) use ($offset) {
            return FileSystem::normalizePath($offset . '/' . $path);
        };
        return $this->replaceRelativeUrls($cssContent, $callback);
    }

    /**
     * A generic method for applying certain callback to all found relative URLs in CSS content
     *
     * Traverse through all relative URLs and apply a callback to each path
     * The $inlineCallback is a user function that obtains the URL value and must return a replacement
     *
     * @param string $cssContent
     * @param callback $inlineCallback
     * @return string
     */
    public function replaceRelativeUrls($cssContent, $inlineCallback)
    {
        $patterns = self::extractRelativeUrls($cssContent);
        if ($patterns) {
            $replace = [];
            foreach ($patterns as $pattern => $path) {
                if (!isset($replace[$pattern])) {
                    $newPath = call_user_func($inlineCallback, $path);
                    $newPattern = str_replace($path, $newPath, $pattern);
                    $replace[$pattern] = $newPattern;
                }
            }
            if ($replace) {
                $cssContent = str_replace(array_keys($replace), array_values($replace), $cssContent);
            }
        }
        return $cssContent;
    }

    /**
     * Extract all "import" directives from CSS-content and put them to the top of document
     *
     * @param string $cssContent
     * @return string
     */
    public function aggregateImportDirectives($cssContent)
    {
        $parts = preg_split('/(@import\s.+?;\s*)/', $cssContent, -1, PREG_SPLIT_DELIM_CAPTURE);
        $imports = [];
        $css = [];
        foreach ($parts as $part) {
            if (0 === strpos($part, '@import', 0)) {
                $imports[] = trim($part);
            } else {
                $css[] = $part;
            }
        }

        $result = implode($css);
        if ($imports) {
            $result = implode("\n", $imports)
                . "\n/* The above import directives are aggregated from content. */\n"
                . $result;
        }
        return $result;
    }

    /**
     * Subroutine for obtaining url() fragments from the CSS content
     *
     * @param string $cssContent
     * @return array
     */
    private static function extractRelativeUrls($cssContent)
    {
        preg_match_all(self::REGEX_CSS_RELATIVE_URLS, $cssContent, $matches);
        if (!empty($matches[0]) && !empty($matches[1])) {
            return array_combine($matches[0], $matches[1]);
        }
        return [];
    }
}
