<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Service\V1\DownloadableLink\Data;

use Magento\Framework\Api\AbstractExtensibleObject;

/**
 * @codeCoverageIgnore
 */
class DownloadableLinkContent extends AbstractExtensibleObject
{
    const TITLE = 'title';
    const PRICE = 'price';
    const NUMBER_OF_DOWNLOADS = 'number_of_downloads';
    const UNLIMITED = 'unlimited';
    const SHAREABLE = 'shareable';
    const SORT_ORDER = 'sort_order';
    const LINK_FILE = 'link_file';
    const LINK_URL = 'link_url';
    const LINK_TYPE = 'link_type';
    const SAMPLE_FILE = 'file';
    const SAMPLE_URL = 'url';
    const SAMPLE_TYPE = 'sample_type';

    /**
     * Retrieve link title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_get(self::TITLE);
    }

    /**
     * Retrieve link sort order
     *
     * @return int
     */
    public function getSortOrder()
    {
        return $this->_get(self::SORT_ORDER);
    }

    /**
     * Retrieve link price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->_get(self::PRICE);
    }

    /**
     * Retrieve number of allowed downloads of the link
     *
     * @return int
     */
    public function getNumberOfDownloads()
    {
        return $this->_get(self::NUMBER_OF_DOWNLOADS);
    }

    /**
     * Check if link is shareable
     *
     * @return bool
     */
    public function isShareable()
    {
        return $this->_get(self::SHAREABLE);
    }

    /**
     * Retrieve link file content
     *
     * @return \Magento\Downloadable\Service\V1\Data\FileContent|null
     */
    public function getLinkFile()
    {
        return $this->_get(self::LINK_FILE);
    }

    /**
     * Retrieve link URL
     *
     * @return string|null
     */
    public function getLinkUrl()
    {
        return $this->_get(self::LINK_URL);
    }

    /**
     * Retrieve link type ('url' or 'file')
     *
     * @return string|null
     */
    public function getLinkType()
    {
        return $this->_get(self::LINK_TYPE);
    }

    /**
     * Retrieve sample file content
     *
     * @return \Magento\Downloadable\Service\V1\Data\FileContent|null
     */
    public function getSampleFile()
    {
        return $this->_get(self::SAMPLE_FILE);
    }

    /**
     * Retrieve sample URL
     *
     * @return string|null
     */
    public function getSampleUrl()
    {
        return $this->_get(self::SAMPLE_URL);
    }

    /**
     * Retrieve sample type ('url' or 'file')
     *
     * @return string|null
     */
    public function getSampleType()
    {
        return $this->_get(self::SAMPLE_TYPE);
    }
}
