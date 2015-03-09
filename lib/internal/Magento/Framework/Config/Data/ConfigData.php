<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Config\Data;

/**
 * Data transfer object to store config data for config options
 */
class ConfigData
{
    /**
     * File key
     *
     * @var string
     */
    private $fileKey;

    /**
     * Segment key
     *
     * @var string
     */
    private $segmentKey;

    /**
     * Data
     *
     * @var array
     */
    private $data;

    /**
     * Constructor
     *
     * @param string $fileKey
     * @param string $segmentKey
     * @param array $data
     */
    public function __construct($fileKey, $segmentKey, array $data)
    {
        $this->fileKey = $fileKey;
        $this->segmentKey = $segmentKey;
        $this->data = $data;
    }

    /**
     * Gets File Key
     *
     * @return string
     */
    public function getFileKey()
    {
        return $this->fileKey;
    }

    /**
     * Gets Segment Key
     *
     * @return string
     */
    public function getSegmentKey()
    {
        return $this->segmentKey;
    }

    /**
     * Gets Data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}

