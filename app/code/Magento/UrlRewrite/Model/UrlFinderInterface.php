<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\UrlRewrite\Model;

/**
 * Url Finder Interface
 * @api
 * @since 100.0.2
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
