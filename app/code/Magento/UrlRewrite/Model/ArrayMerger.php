<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Model;

use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Merges an array of Url Rewrites
 */
class ArrayMerger
{
    /**
     * @var $rewritesArray[]
     */
    private $data = [];

    /**
     * Adds url rewrites to class data container merging with previous data by keys
     *
     * @param UrlRewrite[] $urlRewritesArray
     * @return void
     */
    public function addData($urlRewritesArray)
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
     * Returns the data added and resets the container to an empty array
     *
     * @return UrlRewrite[]
     */
    public function getResetData()
    {
        $result = $this->data;
        unset($this->data);
        $this->data = [];
        return $result;
    }
}
