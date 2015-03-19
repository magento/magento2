<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Api\Data;

/**
 * @codeCoverageIgnore
 */
interface LinkContentInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Retrieve sample title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set sample title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * Retrieve sample sort order
     *
     * @return int
     */
    public function getSortOrder();

    /**
     * Set sample sort order
     *
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder);

    /**
     * Retrieve link price
     *
     * @return string
     */
    public function getPrice();

    /**
     * Set link price
     *
     * @param string $price
     * @return $this
     */
    public function setPrice($price);

    /**
     * Retrieve number of allowed downloads of the link
     *
     * @return int
     */
    public function getNumberOfDownloads();

    /**
     * Set number of allowed downloads of the link
     *
     * @param int $numberOfDownloads
     * @return $this
     */
    public function setNumberOfDownloads($numberOfDownloads);

    /**
     * Check if link is shareable
     *
     * @return bool
     */
    public function isShareable();

    /**
     * Set whether link is shareable
     *
     * @param bool $shareable
     * @return $this
     */
    public function setShareable($shareable);

    /**
     * Retrieve link file content
     *
     * @return \Magento\Downloadable\Api\Data\File\ContentInterface|null
     */
    public function getLinkFile();

    /**
     * Set link file content
     *
     * @param \Magento\Downloadable\Api\Data\File\ContentInterface $linkFile
     * @return $this
     */
    public function setLinkFile(\Magento\Downloadable\Api\Data\File\ContentInterface $linkFile = null);

    /**
     * Retrieve link URL
     *
     * @return string|null
     */
    public function getLinkUrl();

    /**
     * Set link URL
     *
     * @param string $linkUrl
     * @return $this
     */
    public function setLinkUrl($linkUrl);

    /**
     * Retrieve link type ('url' or 'file')
     *
     * @return string|null
     */
    public function getLinkType();

    /**
     * Set link type ('url' or 'file')
     *
     * @param string $linkType
     * @return $this
     */
    public function setLinkType($linkType);

    /**
     * Retrieve sample file content
     *
     * @return \Magento\Downloadable\Api\Data\File\ContentInterface|null
     */
    public function getSampleFile();

    /**
     * Retrieve sample file content
     *
     * @param \Magento\Downloadable\Api\Data\File\ContentInterface $sampleFile
     * @return $this
     */
    public function setSampleFile(\Magento\Downloadable\Api\Data\File\ContentInterface $sampleFile = null);

    /**
     * Retrieve sample URL
     *
     * @return string|null
     */
    public function getSampleUrl();

    /**
     * Set sample URL
     *
     * @param string $sampleUrl
     * @return $this
     */
    public function setSampleUrl($sampleUrl);

    /**
     * Retrieve sample type ('url' or 'file')
     *
     * @return string|null
     */
    public function getSampleType();

    /**
     * Set sample type ('url' or 'file')
     *
     * @param string $sampleType
     * @return $this
     */
    public function setSampleType($sampleType);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Downloadable\Api\Data\LinkContentExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Downloadable\Api\Data\LinkContentExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Downloadable\Api\Data\LinkContentExtensionInterface $extensionAttributes
    );
}
