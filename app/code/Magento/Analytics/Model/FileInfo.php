<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return string
     */
    public function getInitializationVector()
    {
        return $this->initializationVector;
    }

    /**
     * @param string $initializationVector
     * @return $this
     */
    public function setInitializationVector($initializationVector)
    {
        $this->initializationVector = $initializationVector;
        return $this;
    }
}
