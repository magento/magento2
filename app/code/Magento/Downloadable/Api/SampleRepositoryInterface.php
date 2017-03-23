<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Api;

use Magento\Downloadable\Api\Data\SampleInterface;

/**
 * Interface SampleRepositoryInterface
 * @api
 */
interface SampleRepositoryInterface
{
    /**
     * List of samples for downloadable product
     *
     * @param string $sku
     * @return \Magento\Downloadable\Api\Data\SampleInterface[]
     */
    public function getList($sku);

    /**
     * List of links with associated samples
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return \Magento\Downloadable\Api\Data\SampleInterface[]
     */
    public function getSamplesByProduct(\Magento\Catalog\Api\Data\ProductInterface $product);

    /**
     * Update downloadable sample of the given product
     *
     * @param string $sku
     * @param \Magento\Downloadable\Api\Data\SampleInterface $sample
     * @param bool $isGlobalScopeContent
     * @return int
     */
    public function save(
        $sku,
        SampleInterface $sample,
        $isGlobalScopeContent = true
    );

    /**
     * Delete downloadable sample
     *
     * @param int $id
     * @return bool
     */
    public function delete($id);
}
