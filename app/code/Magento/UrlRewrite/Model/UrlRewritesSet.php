<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Model;

use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Removes duplicates for a set/array of Url Rewrites
 */
class UrlRewritesSet
{
    /**
     * @var $rewritesArray[]
     */
    private $data = [];

    /**
     * Adds url rewrites to class data container by removing duplicates by a unique key
     *
     * @param UrlRewrite[] $urlRewritesArray
     * @return void
     */
    public function merge($urlRewritesArray)
    {
        $separator = '_';
        foreach ($urlRewritesArray as $urlRewrite) {
            $key = $urlRewrite->getRequestPath() . $separator . $urlRewrite->getStoreId();
            if ($key !== $separator) {
                $this->data[$urlRewrite->getRequestPath() . $separator . $urlRewrite->getStoreId()] = $urlRewrite;
            } else {
                $this->data[] = $urlRewrite;
            }
        }
    }

    /**
     * Returns the data added to container
     *
     * @return UrlRewrite[]
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Resets the container to an empty array
     *
     * @return void
     */
    public function resetData()
    {
        unset($this->data);
        $this->data = [];
    }
}
