<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Service\V1\DownloadableSample\Data;

use Magento\Framework\Api\AbstractExtensibleObject;

/**
 * @codeCoverageIgnore
 */
class DownloadableSampleContent extends AbstractExtensibleObject
{
    const TITLE = 'title';
    const SORT_ORDER = 'sort_order';
    const SAMPLE_FILE = 'file';
    const SAMPLE_URL = 'url';
    const SAMPLE_TYPE = 'sample_type';

    /**
     * Retrieve sample title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_get(self::TITLE);
    }

    /**
     * Retrieve sample type ('url' or 'file')
     *
     * @return string|null
     */
    public function getSampleType()
    {
        return $this->_get(self::SAMPLE_TYPE);
    }

    /**
     * Retrieve sample file content
     *
     * @return \Magento\Downloadable\Service\V1\Data\FileContent|null
     */
    public function getSampleFile()
    {
        return $this->_get(self::SAMPLE_FILE);
    }

    /**
     * Retrieve sample sort order
     *
     * @return int
     */
    public function getSortOrder()
    {
        return $this->_get(self::SORT_ORDER);
    }

    /**
     * Retrieve sample URL
     *
     * @return string|null
     */
    public function getSampleUrl()
    {
        return $this->_get(self::SAMPLE_URL);
    }
}
