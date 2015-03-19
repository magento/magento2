<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Api\Data;

/**
 * @codeCoverageIgnore
 */
interface SampleContentInterface extends \Magento\Framework\Api\ExtensibleDataInterface
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
     * Retrieve sample file content
     *
     * @return \Magento\Downloadable\Api\Data\File\ContentInterface|null
     */
    public function getSampleFile();

    /**
     * Set sample file content
     *
     * @param \Magento\Downloadable\Api\Data\File\ContentInterface $sampleFile
     * @return $this
     */
    public function setSampleFile(\Magento\Downloadable\Api\Data\File\ContentInterface $sampleFile = null);

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
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Downloadable\Api\Data\SampleContentExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Downloadable\Api\Data\SampleContentExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Downloadable\Api\Data\SampleContentExtensionInterface $extensionAttributes
    );
}
