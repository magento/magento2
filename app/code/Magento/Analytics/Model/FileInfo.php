<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

/**
 * Contain information about encrypted file.
 * @since 2.2.0
 */
class FileInfo
{
    /**
     * Initialization vector that was used for encryption.
     *
     * @var string
     * @since 2.2.0
     */
    private $initializationVector;

    /**
     * Relative path to an encrypted file.
     *
     * @var string
     * @since 2.2.0
     */
    private $path;

    /**
     * @param string $path
     * @param string $initializationVector
     * @since 2.2.0
     */
    public function __construct($path = '', $initializationVector = '')
    {
        $this->path = $path;
        $this->initializationVector = $initializationVector;
    }

    /**
     * @return string
     * @since 2.2.0
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string
     * @since 2.2.0
     */
    public function getInitializationVector()
    {
        return $this->initializationVector;
    }
}
