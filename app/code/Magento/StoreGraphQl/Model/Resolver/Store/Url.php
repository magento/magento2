<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StoreGraphQl\Model\Resolver\Store;

use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Url\Validator as UrlValidator;
use Magento\Framework\Url\RouteValidator as PathValidator;
use Magento\Framework\Validation\ValidationException;

/**
 * Service class for scoped urls and paths
 */
class Url
{
    /** @var UrlValidator */
    private $urlValidator;

    /** @var PathValidator */
    private $pathValidator;

    /** @var UrlInterface */
    private $urlInterface;

    /**
     * @param UrlValidator $urlValidator
     * @param PathValidator $pathValidator
     * @param UrlInterface $urlInterface
     */
    public function __construct(
        UrlValidator $urlValidator,
        PathValidator $pathValidator,
        UrlInterface $urlInterface
    ) {
        $this->urlValidator = $urlValidator;
        $this->pathValidator = $pathValidator;
        $this->urlInterface = $urlInterface;
    }

    /**
     * Validate path/route
     *
     * @param string $path
     * @return bool
     */
    public function isPath(string $path): bool
    {
        return $this->pathValidator->isValid($path);
    }

    /**
     * Get full url with base path from a path
     *
     * @param string $path
     * @param StoreInterface $store
     * @param bool $isSecure
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
            || !$this->validateUrl($resultUrl)
        ) {
            throw new ValidationException(__('Invalid Url.'));
        }

        return $resultUrl;
    }

    /**
     * Validate redirect Urls
     *
     * @param array $urls
     * @return boolean
     */
    private function validateUrl(string $url): bool
    {
        if (!$this->urlValidator->isValid($url)) {
            return false;
        }
        return true;
    }
}
