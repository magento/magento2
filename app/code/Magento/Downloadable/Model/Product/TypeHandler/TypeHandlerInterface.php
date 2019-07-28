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
 * @since 100.0.2
 */
interface TypeHandlerInterface
{
    /**
     * @param Product $product
     * @param array $data
     * @return void
     */
    public function save(Product $product, array $data);

    /**
     * @param array $data
     * @return bool
     */
    public function isCanHandle(array $data);
}
