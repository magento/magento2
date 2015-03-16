<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Link;

use Magento\Downloadable\Api\Data\LinkContentInterface;

/**
 * @codeCoverageIgnore
 */
class Content extends \Magento\Framework\Model\AbstractExtensibleModel implements LinkContentInterface
{
    const TITLE = 'title';
    const PRICE = 'price';
    const NUMBER_OF_DOWNLOADS = 'number_of_downloads';
    const SHAREABLE = 'shareable';
    const SORT_ORDER = 'sort_order';
    const LINK_FILE = 'link_file';
    const LINK_URL = 'link_url';
    const LINK_TYPE = 'link_type';
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
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getPrice()
    {
        return $this->getData(self::PRICE);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getNumberOfDownloads()
    {
        return $this->getData(self::NUMBER_OF_DOWNLOADS);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function isShareable()
    {
        return $this->getData(self::SHAREABLE);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getLinkFile()
    {
        return $this->getData(self::LINK_FILE);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getLinkUrl()
    {
        return $this->getData(self::LINK_URL);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getLinkType()
    {
        return $this->getData(self::LINK_TYPE);
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
    public function getSampleUrl()
    {
        return $this->getData(self::SAMPLE_URL);
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
     * Set link price
     *
     * @param string $price
     * @return $this
     */
    public function setPrice($price)
    {
        return $this->setData(self::PRICE, $price);
    }

    /**
     * Set number of allowed downloads of the link
     *
     * @param int $numberOfDownloads
     * @return $this
     */
    public function setNumberOfDownloads($numberOfDownloads)
    {
        return $this->setData(self::NUMBER_OF_DOWNLOADS, $numberOfDownloads);
    }

    /**
     * Set whether link is shareable
     *
     * @param bool $shareable
     * @return $this
     */
    public function setShareable($shareable)
    {
        return $this->setData(self::SHAREABLE, $shareable);
    }

    /**
     * Set link file content
     *
     * @param \Magento\Downloadable\Api\Data\File\ContentInterface $linkFile
     * @return $this
     */
    public function setLinkFile(\Magento\Downloadable\Api\Data\File\ContentInterface $linkFile = null)
    {
        return $this->setData(self::LINK_FILE, $linkFile);
    }

    /**
     * Set link URL
     *
     * @param string $linkUrl
     * @return $this
     */
    public function setLinkUrl($linkUrl)
    {
        return $this->setData(self::LINK_URL, $linkUrl);
    }

    /**
     * Set link type ('url' or 'file')
     *
     * @param string $linkType
     * @return $this
     */
    public function setLinkType($linkType)
    {
        return $this->setData(self::LINK_TYPE, $linkType);
    }

    /**
     * Retrieve sample file content
     *
     * @param \Magento\Downloadable\Api\Data\File\ContentInterface $sampleFile
     * @return $this
     */
    public function setSampleFile(\Magento\Downloadable\Api\Data\File\ContentInterface $sampleFile = null)
    {
        return $this->setData(self::SAMPLE_FILE, $sampleFile);
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
     * {@inheritdoc}
     *
     * @return \Magento\Downloadable\Api\Data\LinkContentExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Downloadable\Api\Data\LinkContentExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Downloadable\Api\Data\LinkContentExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
