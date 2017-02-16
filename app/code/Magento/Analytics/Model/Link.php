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
    private $iv;

    /**
     * Link constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->url = array_key_exists('url', $data) ? $data['url'] : null;
        $this->iv = array_key_exists('iv', $data) ? $data['iv'] : null;
    }

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
    public function getIV()
    {
        return $this->iv;
    }
}
