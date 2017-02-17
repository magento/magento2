<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

use Magento\Analytics\Api\Data\LinkInterface;

/**
 * Class Link
 *
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
    private $initializedVector;

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getInitializedVector()
    {
        return $this->initializedVector;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @param string $initializedVector
     */
    public function setInitializedVector($initializedVector)
    {
        $this->initializedVector = $initializedVector;
    }
}
