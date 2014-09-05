<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Downloadable\Service\V1\DownloadableLink\Data;

use \Magento\Framework\Service\Data\AbstractExtensibleObject;

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
