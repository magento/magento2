<?php
/**
 * Downloadable Link Builder
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

use \Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder;
use \Magento\Downloadable\Service\V1\Data\FileContent;

/**
 * @codeCoverageIgnore
 */
class DownloadableLinkContentBuilder extends AbstractExtensibleObjectBuilder
{
    /**
     * Set link title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        return $this->_set(DownloadableLinkContent::TITLE, $title);
    }

    /**
     * Set link sort order
     *
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder)
    {
        return $this->_set(DownloadableLinkContent::SORT_ORDER, $sortOrder);
    }

    /**
     * Set link price
     *
     * @param string $price
     * @return $this
     */
    public function setPrice($price)
    {
        return $this->_set(DownloadableLinkContent::PRICE, $price);
    }

    /**
     * Set number of allowed downloads of the link
     *
     * @param int $numberOfDownloads
     * @return $this
     */
    public function setNumberOfDownloads($numberOfDownloads)
    {
        return $this->_set(DownloadableLinkContent::NUMBER_OF_DOWNLOADS, $numberOfDownloads);
    }

    /**
     * Check if link is shareable
     *
     * @param bool $shareable
     * @return $this
     */
    public function setShareable($shareable)
    {
        return $this->_set(DownloadableLinkContent::SHAREABLE, $shareable);
    }

    /**
     * Set link file content
     *
     * @param FileContent $linkFile
     * @return $this
     */
    public function setLinkFile(FileContent $linkFile)
    {
        return $this->_set(DownloadableLinkContent::LINK_FILE, $linkFile);
    }

    /**
     * Set link URL
     *
     * @param string $linkUrl
     * @return $this
     */
    public function setLinkUrl($linkUrl)
    {
        return $this->_set(DownloadableLinkContent::LINK_URL, $linkUrl);
    }

    /**
     * Set link type ('url' or 'file')
     *
     * @param string $linkType
     * @return $this
     */
    public function setLinkType($linkType)
    {
        return $this->_set(DownloadableLinkContent::LINK_TYPE, $linkType);
    }

    /**
     * Set sample file content
     *
     * @param FileContent $sampleFile
     * @return $this
     */
    public function setSampleFile($sampleFile)
    {
        return $this->_set(DownloadableLinkContent::SAMPLE_FILE, $sampleFile);
    }

    /**
     * Set sample URL
     *
     * @param string $sampleUrl
     * @return $this
     */
    public function setSampleUrl($sampleUrl)
    {
        return $this->_set(DownloadableLinkContent::SAMPLE_URL, $sampleUrl);
    }

    /**
     * Set sample type ('url' or 'file')
     *
     * @param string $sampleType
     * @return $this
     */
    public function setSampleType($sampleType)
    {
        return $this->_set(DownloadableLinkContent::SAMPLE_TYPE, $sampleType);
    }
}
