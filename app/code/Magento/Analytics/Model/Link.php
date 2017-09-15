<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

use Magento\Analytics\Api\Data\LinkInterface;

/**
 * Class Link
 *
 * Represents link with collected data and initialized vector for decryption.
 * @since 2.2.0
 */
class Link implements LinkInterface
{
    /**
     * @var string
     * @since 2.2.0
     */
    private $url;

    /**
     * @var string
     * @since 2.2.0
     */
    private $initializationVector;

    /**
     * Link constructor.
     *
     * @param string $url
     * @param string $initializationVector
     * @since 2.2.0
     */
    public function __construct($url, $initializationVector)
    {
        $this->url = $url;
        $this->initializationVector = $initializationVector;
    }

    /**
     * @return string
     * @since 2.2.0
     */
    public function getUrl()
    {
        return $this->url;
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
