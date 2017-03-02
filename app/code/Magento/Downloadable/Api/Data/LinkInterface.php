<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Api\Data;

/**
 * @codeCoverageIgnore
 * @api
 */
interface LinkInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    /**
     * @return int|null Sample(or link) id
     */
    public function getId();

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * @return string|null
     */
    public function getTitle();

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * @return int
     */
    public function getSortOrder();

    /**
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder);

    /**
     * Link shareable status
     * 0 -- No
     * 1 -- Yes
     * 2 -- Use config default value
     *
     * @return int
     */
    public function getIsShareable();

    /**
     * @param int $isShareable
     * @return $this
     */
    public function setIsShareable($isShareable);

    /**
     * Link price
     *
     * @return float
     */
    public function getPrice();

    /**
     * Set link price
     *
     * @param float $price
     * @return $this
     */
    public function setPrice($price);

    /**
     * Number of downloads per user
     * Null for unlimited downloads
     *
     * @return int|null
     */
    public function getNumberOfDownloads();

    /**
     * Set number of downloads per user
     * Null for unlimited downloads
     *
     * @param int $numberOfDownloads
     * @return $this
     */
    public function setNumberOfDownloads($numberOfDownloads);

    /**
     * @return string
     */
    public function getLinkType();

    /**
     * @param string $linkType
     * @return $this
     */
    public function setLinkType($linkType);

    /**
     * Return file path or null when type is 'url'
     *
     * @return string|null relative file path
     */
    public function getLinkFile();

    /**
     * Set file path or null when type is 'url'
     *
     * @param string $linkFile
     * @return $this
     */
    public function setLinkFile($linkFile);

    /**
     * Return file content
     *
     * @return \Magento\Downloadable\Api\Data\File\ContentInterface|null
     */
    public function getLinkFileContent();

    /**
     * Set file content
     *
     * @param \Magento\Downloadable\Api\Data\File\ContentInterface $linkFileContent
     * @return $this
     */
    public function setLinkFileContent(\Magento\Downloadable\Api\Data\File\ContentInterface $linkFileContent = null);

    /**
     * Return link url or null when type is 'file'
     *
     * @return string|null
     */
    public function getLinkUrl();

    /**
     * Set URL
     *
     * @param string $linkUrl
     * @return $this
     */
    public function setLinkUrl($linkUrl);

    /**
     * @return string
     */
    public function getSampleType();

    /**
     * @param string $sampleType
     * @return $this
     */
    public function setSampleType($sampleType);

    /**
     * Return file path or null when type is 'url'
     *
     * @return string|null relative file path
     */
    public function getSampleFile();

    /**
     * Set file path
     *
     * @param string $sampleFile
     * @return $this
     */
    public function setSampleFile($sampleFile);

    /**
     * Return sample file content when type is 'file'
     *
     * @return \Magento\Downloadable\Api\Data\File\ContentInterface|null relative file path
     */
    public function getSampleFileContent();

    /**
     * Set sample file content
     *
     * @param \Magento\Downloadable\Api\Data\File\ContentInterface $sampleFileContent
     * @return $this
     */
    public function setSampleFileContent(
        \Magento\Downloadable\Api\Data\File\ContentInterface $sampleFileContent = null
    );

    /**
     * Return URL or NULL when type is 'file'
     *
     * @return string|null file URL
     */
    public function getSampleUrl();

    /**
     * Set URL
     *
     * @param string $sampleUrl
     * @return $this
     */
    public function setSampleUrl($sampleUrl);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Downloadable\Api\Data\LinkExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Downloadable\Api\Data\LinkExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Downloadable\Api\Data\LinkExtensionInterface $extensionAttributes);
}
