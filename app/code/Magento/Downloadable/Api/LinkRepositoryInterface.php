<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Api;

use Magento\Downloadable\Api\Data\LinkInterface;

interface LinkRepositoryInterface
{
    /**
     * List of samples for downloadable product
     *
     * @param string $sku
     * @return \Magento\Downloadable\Api\Data\SampleInterface[]
     */
    public function getSamples($sku);

    /**
     * List of samples for downloadable product
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return \Magento\Downloadable\Api\Data\SampleInterface[]
     */
    public function getSamplesByProduct(\Magento\Catalog\Api\Data\ProductInterface $product);

    /**
     * List of links with associated samples
     *
     * @param string $sku
     * @return \Magento\Downloadable\Api\Data\LinkInterface[]
     */
    public function getLinks($sku);

    /**
     * List of links with associated samples
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return \Magento\Downloadable\Api\Data\LinkInterface[]
     */
    public function getLinksByProduct(\Magento\Catalog\Api\Data\ProductInterface $product);

    /**
     * Update downloadable link of the given product (link type and its resources cannot be changed)
     *
     * @param string $sku
     * @param \Magento\Downloadable\Api\Data\LinkInterface $link
     * @param bool $isGlobalScopeContent
     * @return int
     */
    public function save($sku, LinkInterface $link, $isGlobalScopeContent = false);

    /**
     * Delete downloadable link
     *
     * @param int $id
     * @return bool
     */
    public function delete($id);
}
