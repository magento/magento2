<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
