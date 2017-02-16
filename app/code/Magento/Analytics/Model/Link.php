<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

use Magento\Analytics\Api\Data\LinkInterface;

/**
 * Class Link
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
