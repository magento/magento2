<?php
/**
 * Downloadable Link Builder
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Service\V1\DownloadableLink\Data;

use Magento\Downloadable\Service\V1\Data\FileContent;
use Magento\Framework\Api\ExtensibleObjectBuilder;

/**
 * @codeCoverageIgnore
 */
class DownloadableLinkContentBuilder extends ExtensibleObjectBuilder
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
