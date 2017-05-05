<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Model;

/**
 * Class to store url rewrites duplicates that were discovered when we save an entity that has related urls
 *
 * This class should be instantiated just once per request
 * Usually products, categories, cms or other extensions
 */
class UrlDuplicatesRegistry
{

    /** @var \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]  */
    private $urlDuplicates = [];

    /**
     * Clears the url rewrites so it can be reused by other processes
     *
     * @return void
     */
    public function clearUrlDuplicates()
    {
        $this->urlDuplicates = [];
    }

    /**
     * Set the url rewrites duplicates that resulted from a saving process
     *
     * @param array $urlDuplicates
     * @return void
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function addUrlDuplicates(array $urlDuplicates)
    {
        if (empty($this->urlDuplicates)) {
            $this->urlDuplicates = array_merge($this->urlDuplicates, $urlDuplicates);
        }
    }

    /**
     * Returns the stored url rewrites duplicates
     *
     * @return array|\Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    public function getUrlDuplicates()
    {
        return $this->urlDuplicates;
    }
}
