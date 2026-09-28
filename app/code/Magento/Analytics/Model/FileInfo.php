<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model;

/**
 * Contain information about encrypted file.
 */
class FileInfo
{
    /**
     * Initialization vector that was used for encryption.
     *
     * @var string
     */
    private $initializationVector;

    /**
     * Relative path to an encrypted file.
     *
     * @var string
     */
    private $path;

    /**
     * @param string $path
     * @param string $initializationVector
     */
    public function __construct($path = '', $initializationVector = '')
    {
        $this->path = $path;
        $this->initializationVector = $initializationVector;
    }

    /**
     * Retrieve path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
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
