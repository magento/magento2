<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Helper;

/**
 * Path pattern creation helper
 */
class PathPattern
{
    /**
     * Translate pattern with glob syntax into regular expression
     *
     * @param string $path
     * @return string
     */
    public function translatePatternFromGlob($path)
    {
        $pattern = preg_quote($path);
        $pattern = str_replace(['\\?', '\\*'], ['[^/]', '[^/]*'], $pattern);
        $pattern = $this->translateGroupsFromGlob($pattern);
        $pattern = $this->translateCharacterGroupsFromGlob($pattern);
        return $pattern;
    }

    /**
     * Translate groups from escaped glob syntax into regular expression
     * Example: filename\.\{php,css,js\} -> filename\.(?:php|css|js)
     *
     * @param string $pattern
     * @return string
     */
    protected function translateGroupsFromGlob($pattern)
    {
        preg_match_all('~\\\\\\{[^,\\}]+(?:,[^,\\}]+)*\\\\\\}~', $pattern, $matches, PREG_OFFSET_CAPTURE);
        for ($index = count($matches[0]) - 1; $index >= 0; $index -= 1) {
            list($match, $offset) = $matches[0][$index];
            $length = strlen($match);
            $replacement = substr_replace($match, '(?:', 0, 2);
            $replacement = substr_replace($replacement, ')', -2, 2);
            $replacement = str_replace(',', '|', $replacement);
            $pattern = substr_replace($pattern, $replacement, $offset, $length);
        }
        return $pattern;
    }

    /**
     * Translate character groups from glob syntax into regular expression
     * Example: \[\!a\-f\]\.php -> [^a-f]\.php
     *
     * @param string $pattern
     * @return string
     */
    protected function translateCharacterGroupsFromGlob($pattern)
    {
        preg_match_all('~\\\\\\[(\\\\\\!)?[^\\]]+\\\\\\]~i', $pattern, $matches, PREG_OFFSET_CAPTURE);
        for ($index = count($matches[0]) - 1; $index >= 0; $index -= 1) {
            list($match, $offset) = $matches[0][$index];
            $length = strlen($match);
            $exclude = !empty($matches[1][$index]);
            $replacement = substr_replace($match, '[' . ($exclude ? '^' : ''), 0, $exclude ? 4 : 2);
            $replacement = substr_replace($replacement, ']', -2, 2);
            $replacement = str_replace('\\-', '-', $replacement);
            $pattern = substr_replace($pattern, $replacement, $offset, $length);
        }
        return $pattern;
    }
}
