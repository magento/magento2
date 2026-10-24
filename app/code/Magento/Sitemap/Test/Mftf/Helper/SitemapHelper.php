<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sitemap\Test\Mftf\Helper;

use Magento\FunctionalTestingFramework\DataTransport\Protocol\CurlInterface;
use Magento\FunctionalTestingFramework\DataTransport\Protocol\CurlTransport;
use Magento\FunctionalTestingFramework\Helper\Helper;

/**
 * Helper class for sitemap HTTP assertions in MFTF tests
 */
class SitemapHelper extends Helper
{
    /**
     * Check HTTP status code for a given URL
     *
     * @param string $url
     * @return int
     */
    public function getHttpStatusCode(string $url): int
    {
        $curl = new CurlTransport();

        try {
            $curl->addOption(CURLOPT_FOLLOWLOCATION, false);
            $curl->addOption(CURLOPT_NOBODY, true); // HEAD request
            $curl->write($url, [], CurlInterface::GET);
            try {
                $curl->read();
            } catch (\Exception $e) {
                // Ignore exception - we just want the status code
            }
            return (int) $curl->getInfo(CURLINFO_HTTP_CODE);
        } catch (\Exception $e) {
            return 0;
        } finally {
            $curl->close();
        }
    }

    /**
     * Check if URL returns an image content type
     *
     * @param string $url
     * @return bool
     */
    public function isImageContentType(string $url): bool
    {
        $curl = new CurlTransport();
        try {
            $curl->addOption(CURLOPT_FOLLOWLOCATION, false);
            $curl->addOption(CURLOPT_NOBODY, true); // HEAD request
            $curl->write($url, [], CurlInterface::GET);
            try {
                $curl->read();
            } catch (\Exception $e) {
                // Ignore exception - we just want the content type
            }
            $contentType = $curl->getInfo(CURLINFO_CONTENT_TYPE);
            return str_contains(strtolower($contentType ?: ''), 'image/');
        } catch (\Exception $e) {
            return false;
        } finally {
            $curl->close();
        }
    }

    /**
     * Check if response body contains specific text
     *
     * @param string $url
     * @param string $searchText
     * @return bool
     */
    public function responseContains(string $url, string $searchText): bool
    {
        $curl = new CurlTransport();

        try {
            $curl->addOption(CURLOPT_FOLLOWLOCATION, false);
            $curl->write($url, [], CurlInterface::GET);
            // Note: read() executes the request and throws exception if HTTP code
            // is not in SUCCESSFUL_HTTP_CODES (200-205), so non-2xx responses return false
            $responseBody = $curl->read();
            return str_contains($responseBody ?: '', $searchText);
        } catch (\Exception $e) {
            return false;
        } finally {
            $curl->close();
        }
    }
}
