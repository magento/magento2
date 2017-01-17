<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Model;

use Magento\UrlRewrite\Service\V1\Data\UrlRewrite as UrlRewriteService;

/**
 * This class is to be used as a container for new generated url rewrites by adding new ones using merge method
 * Removes duplicates for a set/array of Url Rewrites based on the unique key of the url_rewrites table
 *
 */
class MergeDataProvider
{
    const SEPARATOR = '_';

    /**
     * @var $rewritesArray[]
     */
    private $data = [];

    /**
     * Adds url rewrites to class data container by removing duplicates by a unique key
     *
     * @param UrlRewriteService[] $urlRewritesArray
     * @return void
     */
    public function merge(array $urlRewritesArray)
    {
        foreach ($urlRewritesArray as $urlRewrite) {
            $key = $urlRewrite->getRequestPath() . self::SEPARATOR . $urlRewrite->getStoreId();
            if ($key !== self::SEPARATOR) {
                $this->data[$key] = $urlRewrite;
            } else {
                $this->data[] = $urlRewrite;
            }
        }
    }

    /**
     * Returns the data added to container
     *
     * @return UrlRewriteService[]
     */
    public function getData()
    {
        return $this->data;
    }
}
