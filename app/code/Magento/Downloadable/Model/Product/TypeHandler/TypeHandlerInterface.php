<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Product\TypeHandler;

use Magento\Catalog\Model\Product;

/**
 * Interface TypeHandlerInterface
 * @api
 * @since 2.0.0
 */
interface TypeHandlerInterface
{
    /**
     * @param Product $product
     * @param array $data
     * @return void
     * @since 2.0.0
     */
    public function save(Product $product, array $data);

    /**
     * @param array $data
     * @return bool
     * @since 2.0.0
     */
    public function isCanHandle(array $data);
}
