<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model\Resolver\Store;

use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Url\Validator as UrlValidator;
use Magento\Framework\Validation\ValidationException;

/**
 * Service class for scoped urls and paths
 */
class Url
{
    /** @var UrlValidator */
    private $urlValidator;

    /** @var UrlInterface */
    private $urlInterface;

    /**
     * @param UrlValidator $urlValidator
     * @param UrlInterface $urlInterface
     */
    public function __construct(
        UrlValidator $urlValidator,
        UrlInterface $urlInterface
    ) {
        $this->urlValidator = $urlValidator;
        $this->urlInterface = $urlInterface;
    }

    /**
     * Validate path
     *
     * @param string $path
     * @return bool
     */
    public function isPath(string $path): bool
    {
        $result = true;

        if (empty($path)) {
            $result = false;
        } elseif ($path[0] == '/'
            || $this->containsProtocolDelimiter($path)
            || $this->containsDirectoryTraversal($path)
            || $this->containsParametersDelimiter($path)
            || $this->startsWithPortDelimiter($path)
            || $this->isUrl($path)) {
            $result = false;
        }
        return $result;
    }

    /**
     * Validate url format
     *
     * @param array $url
     * @return boolean
     */
    private function isUrl(string $url): bool
    {
        $result = false;
        if ($this->urlValidator->isValid($url)) {
            $result = true;
        }
        return $result;
    }

    /**
     * Get full url with base path from a path
     *
     * @param string $path
     * @param StoreInterface $store
     * @return string
     * @throws ValidationException
     */
    public function getUrlFromPath(string $path, StoreInterface $store): string
    {
        //if it's a url then don't proceed with further validation
        if (!$this->isPath($path)) {
            throw new ValidationException(__('Invalid Url.'));
        }

        $params = ["_secure" => $store->isCurrentlySecure()];
        $this->urlInterface->setScope($store);

        $baseUrl = $this->urlInterface->getBaseUrl($params);
        $resultUrl = $this->urlInterface->getUrl($path, $params);

        // validate the resulting url
        if (substr($resultUrl, 0, strlen($baseUrl)) != $baseUrl
            || $path == $resultUrl
            || $resultUrl == $baseUrl
            || !$this->isUrl($resultUrl)
        ) {
            throw new ValidationException(__('Invalid Url.'));
        }

        return $resultUrl;
    }

    /**
     * Validate if url contains protocol delimiter
     *
     * @param array $url
     * @return boolean
     */
    private function containsProtocolDelimiter(string $url): bool
    {
        if (strpos($url, '://') !== false) {
            return true;
        }
        return false;
    }

    /**
     * Validate if url contains directory traversal
     *
     * @param array $url
     * @return boolean
     */
    private function containsDirectoryTraversal(string $url): bool
    {
        if (strpos($url, '..') !== false) {
            return true;
        }
        return false;
    }

    /**
     * Validate if url contains parameters delimiter
     *
     * @param array $url
     * @return boolean
     */
    private function containsParametersDelimiter(string $url): bool
    {
        if (strpos($url, '?') !== false) {
            return true;
        }
        return false;
    }

    /**
     * Validate if path starts with port delimiter
     *
     * @param array $path
     * @return boolean
     */
    private function startsWithPortDelimiter(string $path): bool
    {
        if (preg_match('/^\:[0-9]+/', $path)) {
            return true;
        }
        return false;
    }
}
