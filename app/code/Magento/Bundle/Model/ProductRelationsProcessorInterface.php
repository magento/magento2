<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model;

/**
 * Processor to handle bundle product relations.
 */
interface ProductRelationsProcessorInterface
{
    /**
     * Process bundle product relations.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param array $existingProductOptions
     * @param array $expectedProductOptions
     * @return void
     */
    public function process(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        array $existingProductOptions,
        array $expectedProductOptions
    ): void;
}
