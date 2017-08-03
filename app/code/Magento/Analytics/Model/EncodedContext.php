<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

/**
 * Contain information about encrypted data.
 * @since 2.2.0
 */
class EncodedContext
{
    /**
     * Encrypted string.
     *
     * @var string
     * @since 2.2.0
     */
    private $content;

    /**
     * Initialization vector that was used for encryption.
     *
     * @var string
     * @since 2.2.0
     */
    private $initializationVector;

    /**
     * @param string $content
     * @param string $initializationVector
     * @since 2.2.0
     */
    public function __construct($content, $initializationVector = '')
    {
        $this->content = $content;
        $this->initializationVector = $initializationVector;
    }

    /**
     * @return string
     * @since 2.2.0
     */
    public function getContent()
    {
        return $this->content;
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
