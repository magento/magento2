<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Api;

use Magento\Downloadable\Api\Data\LinkInterface;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Interface LinkRepositoryInterface
 * @package Magento\Downloadable\Api
 * @api
 * @since 2.0.0
 */
interface LinkRepositoryInterface
{
    /**
     * List of links with associated samples
     *
     * @param string $sku
     * @return \Magento\Downloadable\Api\Data\LinkInterface[]
     * @since 2.0.0
     */
    public function getList($sku);

    /**
     * List of links with associated samples
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return \Magento\Downloadable\Api\Data\LinkInterface[]
     * @since 2.0.0
     */
    public function getLinksByProduct(ProductInterface $product);

    /**
     * Update downloadable link of the given product (link type and its resources cannot be changed)
     *
     * @param string $sku
     * @param \Magento\Downloadable\Api\Data\LinkInterface $link
     * @param bool $isGlobalScopeContent
     * @return int
     * @since 2.0.0
     */
    public function save($sku, LinkInterface $link, $isGlobalScopeContent = true);

    /**
     * Delete downloadable link
     *
     * @param int $id
     * @return bool
     * @since 2.0.0
     */
    public function delete($id);
}
