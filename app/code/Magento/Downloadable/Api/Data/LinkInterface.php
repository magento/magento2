<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Api\Data;

/**
 * @codeCoverageIgnore
 * @api
 * @since 2.0.0
 */
interface LinkInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * @return int|null Sample(or link) id
     * @since 2.0.0
     */
    public function getId();

    /**
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setId($id);

    /**
     * @return string|null
     * @since 2.0.0
     */
    public function getTitle();

    /**
     * @param string $title
     * @return $this
     * @since 2.0.0
     */
    public function setTitle($title);

    /**
     * @return int
     * @since 2.0.0
     */
    public function getSortOrder();

    /**
     * @param int $sortOrder
     * @return $this
     * @since 2.0.0
     */
    public function setSortOrder($sortOrder);

    /**
     * Link shareable status
     * 0 -- No
     * 1 -- Yes
     * 2 -- Use config default value
     *
     * @return int
     * @since 2.0.0
     */
    public function getIsShareable();

    /**
     * @param int $isShareable
     * @return $this
     * @since 2.0.0
     */
    public function setIsShareable($isShareable);

    /**
     * Link price
     *
     * @return float
     * @since 2.0.0
     */
    public function getPrice();

    /**
     * Set link price
     *
     * @param float $price
     * @return $this
     * @since 2.0.0
     */
    public function setPrice($price);

    /**
     * Number of downloads per user
     * Null for unlimited downloads
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getNumberOfDownloads();

    /**
     * Set number of downloads per user
     * Null for unlimited downloads
     *
     * @param int $numberOfDownloads
     * @return $this
     * @since 2.0.0
     */
    public function setNumberOfDownloads($numberOfDownloads);

    /**
     * @return string
     * @since 2.0.0
     */
    public function getLinkType();

    /**
     * @param string $linkType
     * @return $this
     * @since 2.0.0
     */
    public function setLinkType($linkType);

    /**
     * Return file path or null when type is 'url'
     *
     * @return string|null relative file path
     * @since 2.0.0
     */
    public function getLinkFile();

    /**
     * Set file path or null when type is 'url'
     *
     * @param string $linkFile
     * @return $this
     * @since 2.0.0
     */
    public function setLinkFile($linkFile);

    /**
     * Return file content
     *
     * @return \Magento\Downloadable\Api\Data\File\ContentInterface|null
     * @since 2.0.0
     */
    public function getLinkFileContent();

    /**
     * Set file content
     *
     * @param \Magento\Downloadable\Api\Data\File\ContentInterface $linkFileContent
     * @return $this
     * @since 2.0.0
     */
    public function setLinkFileContent(\Magento\Downloadable\Api\Data\File\ContentInterface $linkFileContent = null);

    /**
     * Return link url or null when type is 'file'
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getLinkUrl();

    /**
     * Set URL
     *
     * @param string $linkUrl
     * @return $this
     * @since 2.0.0
     */
    public function setLinkUrl($linkUrl);

    /**
     * @return string
     * @since 2.0.0
     */
    public function getSampleType();

    /**
     * @param string $sampleType
     * @return $this
     * @since 2.0.0
     */
    public function setSampleType($sampleType);

    /**
     * Return file path or null when type is 'url'
     *
     * @return string|null relative file path
     * @since 2.0.0
     */
    public function getSampleFile();

    /**
     * Set file path
     *
     * @param string $sampleFile
     * @return $this
     * @since 2.0.0
     */
    public function setSampleFile($sampleFile);

    /**
     * Return sample file content when type is 'file'
     *
     * @return \Magento\Downloadable\Api\Data\File\ContentInterface|null relative file path
     * @since 2.0.0
     */
    public function getSampleFileContent();

    /**
     * Set sample file content
     *
     * @param \Magento\Downloadable\Api\Data\File\ContentInterface $sampleFileContent
     * @return $this
     * @since 2.0.0
     */
    public function setSampleFileContent(
        \Magento\Downloadable\Api\Data\File\ContentInterface $sampleFileContent = null
    );

    /**
     * Return URL or NULL when type is 'file'
     *
     * @return string|null file URL
     * @since 2.0.0
     */
    public function getSampleUrl();

    /**
     * Set URL
     *
     * @param string $sampleUrl
     * @return $this
     * @since 2.0.0
     */
    public function setSampleUrl($sampleUrl);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Downloadable\Api\Data\LinkExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Downloadable\Api\Data\LinkExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Downloadable\Api\Data\LinkExtensionInterface $extensionAttributes);
}
