<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model;

/**
 * Contain information about encrypted data.
 */
class EncodedContext
{
    /**
     * Encrypted string.
     *
     * @var string
     */
    private $content;

    /**
     * Initialization vector that was used for encryption.
     *
     * @var string
     */
    private $initializationVector;

    /**
     * @param string $content
     * @param string $initializationVector
     */
    public function __construct($content, $initializationVector = '')
    {
        $this->content = $content;
        $this->initializationVector = $initializationVector;
    }

    /**
     * Retrieve content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Retrieve initialization vector
     *
     * @return string
     */
    public function getInitializationVector()
    {
        return $this->initializationVector;
    }
}
