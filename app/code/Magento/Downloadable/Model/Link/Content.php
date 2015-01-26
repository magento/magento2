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
}
