<?php
/**
 * Url security information
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url;

/**
 * Class \Magento\Framework\Url\SecurityInfo
 *
 * @since 2.0.0
 */
class SecurityInfo implements \Magento\Framework\Url\SecurityInfoInterface
{
    /**
     * List of secure url patterns
     *
     * @var array
     * @since 2.0.0
     */
    protected $secureUrlsList = [];

    /**
     * List of patterns excluded form secure url list
     * @since 2.0.0
     */
    protected $excludedUrlsList = [];

    /**
     * List of already checked urls
     *
     * @var array
     * @since 2.0.0
     */
    protected $secureUrlsCache = [];

    /**
     * @param string[] $secureUrlList
     * @param string[] $excludedUrlList
     * @since 2.0.0
     */
    public function __construct($secureUrlList = [], $excludedUrlList = [])
    {
        $this->secureUrlsList = $secureUrlList;
        $this->excludedUrlsList = $excludedUrlList;
    }

    /**
     * Check whether url is secure
     *
     * @param string $url
     * @return bool
     * @since 2.0.0
     */
    public function isSecure($url)
    {
        if (!isset($this->secureUrlsCache[$url])) {
            $this->secureUrlsCache[$url] = false;
            foreach ($this->excludedUrlsList as $match) {
                if (strpos($url, (string)$match) === 0) {
                    return $this->secureUrlsCache[$url];
                }
            }
            foreach ($this->secureUrlsList as $match) {
                if (strpos($url, (string)$match) === 0) {
                    $this->secureUrlsCache[$url] = true;
                    break;
                }
            }
        }
        return $this->secureUrlsCache[$url];
    }
}
