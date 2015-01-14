<?php
/**
 * Downloadable Link Builder
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Service\V1\DownloadableSample\Data;

use Magento\Downloadable\Service\V1\Data\FileContent;
use Magento\Framework\Api\ExtensibleObjectBuilder;

/**
 * @codeCoverageIgnore
 */
class DownloadableSampleContentBuilder extends ExtensibleObjectBuilder
{
    /**
     * Set link title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        return $this->_set(DownloadableSampleContent::TITLE, $title);
    }

    /**
     * Set sample file content
     *
     * @param FileContent $sampleFile
     * @return $this
     */
    public function setSampleFile($sampleFile)
    {
        return $this->_set(DownloadableSampleContent::SAMPLE_FILE, $sampleFile);
    }

    /**
     * Set sample type ('url' or 'file')
     *
     * @param string $sampleType
     * @return $this
     */
    public function setSampleType($sampleType)
    {
        return $this->_set(DownloadableSampleContent::SAMPLE_TYPE, $sampleType);
    }

    /**
     * Set sample URL
     *
     * @param string $sampleUrl
     * @return $this
     */
    public function setSampleUrl($sampleUrl)
    {
        return $this->_set(DownloadableSampleContent::SAMPLE_URL, $sampleUrl);
    }

    /**
     * Set sample sort order
     *
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder)
    {
        return $this->_set(DownloadableSampleContent::SORT_ORDER, $sortOrder);
    }
}
