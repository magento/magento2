<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model;

use Magento\Analytics\Api\Data\LinkInterface;

/**
 * Represents link with collected data and initialized vector for decryption.
 */
class Link implements LinkInterface
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $initializationVector;

    /**
     * @param string $url
     * @param string $initializationVector
     */
    public function __construct($url, $initializationVector)
    {
        $this->url = $url;
        $this->initializationVector = $initializationVector;
    }

    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @inheritdoc
     */
    public function getInitializationVector()
    {
        return $this->initializationVector;
    }
}
