<?php
/**
 * Url security information
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url;

class SecurityInfo implements \Magento\Framework\Url\SecurityInfoInterface
{
    /**
     * List of secure url patterns
     *
     * @var array
     */
    protected $secureUrlsList = [];

    /**
     * List of patterns excluded form secure url list
     */
    protected $excludedUrlsList = [];

    /**
     * List of already checked urls
     *
     * @var array
     */
    protected $secureUrlsCache = [];

    /**
     * @param string[] $secureUrlList
     * @param string[] $excludedUrlList
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
