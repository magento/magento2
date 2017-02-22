<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Api\Data;

use \Magento\Downloadable\Api\Data\File\ContentInterface;

/**
 * @codeCoverageIgnore
 */
interface SampleInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    /**
     * Product sample id
     *
     * @return int|null Sample(or link) id
     */
    public function getId();

    /**
     * Set product sample id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Sample title
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
     * Sort order index for sample
     *
     * @return int
     */
    public function getSortOrder();

    /**
     * Set sort order index for sample
     *
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder);

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
     * Set file path or null when type is 'url'
     *
     * @param string $sampleFile
     * @return $this
     */
    public function setSampleFile($sampleFile);

    /**
     * Retrieve sample file content
     *
     * @return \Magento\Downloadable\Api\Data\File\ContentInterface|null
     */
    public function getSampleFileContent();

    /**
     * Set sample file content
     *
     * @param \Magento\Downloadable\Api\Data\File\ContentInterface $sampleFileContent
     * @return $this
     */
    public function setSampleFileContent(ContentInterface $sampleFileContent = null);

    /**
     * Return URL or NULL when type is 'file'
     *
     * @return string|null file URL
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
     * @return \Magento\Downloadable\Api\Data\SampleExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Downloadable\Api\Data\SampleExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Downloadable\Api\Data\SampleExtensionInterface $extensionAttributes
    );
}
