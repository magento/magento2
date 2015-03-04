<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Data transfer object to store config data for config options
 */
class ConfigDada
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
     * @var []
     */
    private $data;

    /**
     * Constructor
     *
     * @param string $fileKey
     * @param string $segmentKey
     * @param [] $data
     */
    public function __construct($fileKey, $segmentKey, $data)
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
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }
}

