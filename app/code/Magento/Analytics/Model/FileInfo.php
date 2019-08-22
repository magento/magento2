<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * @return string
     */
    public function getInitializationVector()
    {
        return $this->initializationVector;
    }
}
