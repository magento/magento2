<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Model;

/**
 * Url Finder Interface
 */
interface UrlFinderInterface
{
    /**
     * Find rewrite by specific data
     *
     * @param array $data
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite|null
     * @api
     */
    public function findOneByData(array $data);

    /**
     * Find rewrites by specific data
     *
     * @param array $data
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     * @api
     */
    public function findAllByData(array $data);
}
