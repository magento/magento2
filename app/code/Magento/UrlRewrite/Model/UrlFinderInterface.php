<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Model;

/**
 * Url Finder Interface
 * @api
 */
interface UrlFinderInterface
{
    /**
     * Find rewrite by specific data
     *
     * @param array $data
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite|null
     */
    public function findOneByData(array $data);

    /**
     * Find rewrites by specific data
     *
     * @param array $data
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    public function findAllByData(array $data);
}
