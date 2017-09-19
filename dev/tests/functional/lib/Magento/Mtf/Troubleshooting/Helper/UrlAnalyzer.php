<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Troubleshooting\Helper;

/**
 * Url analyzer helper for the config file.
 */
class UrlAnalyzer
{
    /**
     * Fix url if it does not have "/" at the end.
     *
     * @param string $key
     * @return array
     */
    public function fixLastSlash($key)
    {
        $message = [];
        $param = sprintf('%s" value="%s', $key, $_ENV[$key]);
        $fileContents = file_get_contents(MTF_PHPUNIT_FILE);
        $lastSymbol = substr($param, -1);
        if ($lastSymbol != '/') {
            $_ENV[$key] = $_ENV[$key] . '/';
            $fileContents = str_replace($param, $param . '/', $fileContents);
            file_put_contents(MTF_PHPUNIT_FILE, $fileContents);
            $message['info'][] = "Slash at the end of url was added in the config file.";
        }
        return $message;
    }

    /**
     * Add/remove 'index.php' as a part of url if needed.
     *
     * @param string $url
     * @return array
     */
    public function resolveIndexPhpProblem($url)
    {
        $fileContents = file_get_contents(MTF_PHPUNIT_FILE);
        $pattern = '/(backend_url.*?=")(.+)"/';
        $replacement = "$1{$url}\"";
        $fileContents = preg_replace($pattern, $replacement, $fileContents);
        file_put_contents(MTF_PHPUNIT_FILE, $fileContents);
        return ['info' => ['"app_backend_url" has been updated in the phpunit.xml.']];
    }

    /**
     * Check if url has subdomains.
     *
     * @param string $url
     * @return array
     */
    public function checkDomain($url)
    {
        $messages = [];
        $pattern = '/([-%\w]*?\.\w+)/';
        if (preg_match($pattern, $url) === false) {
            $messages['error'][] =
                'Instance should have domain name with at least one subdomain to function correctly. Examples:'
                . PHP_EOL . "\tValid: http://magento.dev/, https://mage.local/."
                . PHP_EOL . "\tInvalid: http://localhost/, https://magento/.";
        }

        return $messages;
    }
}
