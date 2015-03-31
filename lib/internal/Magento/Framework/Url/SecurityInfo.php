<?php
/**
 * Url security information
 *
 * Copyright © 2015 Magento. All rights reserved.
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
     * List of already checked urls
     *
     * @var array
     */
    protected $secureUrlsCache = [];

    /**
     * @param string[] $secureUrlList
     */
    public function __construct($secureUrlList = [])
    {
        $this->secureUrlsList = $secureUrlList;
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
