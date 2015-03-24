<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Sample;

use Magento\Downloadable\Api\Data\SampleContentInterface;

/**
 * @codeCoverageIgnore
 */
class Content extends \Magento\Framework\Model\AbstractExtensibleModel implements SampleContentInterface
{
    const TITLE = 'title';
    const SORT_ORDER = 'sort_order';
    const SAMPLE_FILE = 'sample_file';
    const SAMPLE_URL = 'sample_url';
    const SAMPLE_TYPE = 'sample_type';

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getSampleType()
    {
        return $this->getData(self::SAMPLE_TYPE);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getSampleFile()
    {
        return $this->getData(self::SAMPLE_FILE);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getSampleUrl()
    {
        return $this->getData(self::SAMPLE_URL);
    }

    /**
     * Set sample title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Set sample type ('url' or 'file')
     *
     * @param string $sampleType
     * @return $this
     */
    public function setSampleType($sampleType)
    {
        return $this->setData(self::SAMPLE_TYPE, $sampleType);
    }

    /**
     * Set sample file content
     *
     * @param \Magento\Downloadable\Api\Data\File\ContentInterface $sampleFile
     * @return $this
     */
    public function setSampleFile(\Magento\Downloadable\Api\Data\File\ContentInterface $sampleFile = null)
    {
        return $this->setData(self::SAMPLE_FILE, $sampleFile);
    }

    /**
     * Set sample sort order
     *
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    /**
     * Set sample URL
     *
     * @param string $sampleUrl
     * @return $this
     */
    public function setSampleUrl($sampleUrl)
    {
        return $this->setData(self::SAMPLE_URL, $sampleUrl);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Downloadable\Api\Data\SampleContentExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Downloadable\Api\Data\SampleContentExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Downloadable\Api\Data\SampleContentExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
