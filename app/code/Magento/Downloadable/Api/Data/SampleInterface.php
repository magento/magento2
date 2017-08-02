<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Api\Data;

use Magento\Downloadable\Api\Data\File\ContentInterface;

/**
 * @codeCoverageIgnore
 * @api
 * @since 2.0.0
 */
interface SampleInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Product sample id
     *
     * @return int|null Sample(or link) id
     * @since 2.0.0
     */
    public function getId();

    /**
     * Set product sample id
     *
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setId($id);

    /**
     * Sample title
     *
     * @return string
     * @since 2.0.0
     */
    public function getTitle();

    /**
     * Set sample title
     *
     * @param string $title
     * @return $this
     * @since 2.0.0
     */
    public function setTitle($title);

    /**
     * Sort order index for sample
     *
     * @return int
     * @since 2.0.0
     */
    public function getSortOrder();

    /**
     * Set sort order index for sample
     *
     * @param int $sortOrder
     * @return $this
     * @since 2.0.0
     */
    public function setSortOrder($sortOrder);

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
     * Set file path or null when type is 'url'
     *
     * @param string $sampleFile
     * @return $this
     * @since 2.0.0
     */
    public function setSampleFile($sampleFile);

    /**
     * Retrieve sample file content
     *
     * @return \Magento\Downloadable\Api\Data\File\ContentInterface|null
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
    public function setSampleFileContent(ContentInterface $sampleFileContent = null);

    /**
     * Return URL or NULL when type is 'file'
     *
     * @return string|null file URL
     * @since 2.0.0
     */
    public function getSampleUrl();

    /**
     * Set sample URL
     *
     * @param string $sampleUrl
     * @return $this
     * @since 2.0.0
     */
    public function setSampleUrl($sampleUrl);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Downloadable\Api\Data\SampleExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Downloadable\Api\Data\SampleExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Downloadable\Api\Data\SampleExtensionInterface $extensionAttributes
    );
}
