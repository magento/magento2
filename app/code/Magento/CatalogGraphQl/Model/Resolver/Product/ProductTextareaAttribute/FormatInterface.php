<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product\ProductTextareaAttribute;

use Magento\Catalog\Model\Product as ModelProduct;

interface FormatInterface
{
    /**
     * @param ModelProduct $product
     * @param string $fieldName
     * @return string
     */
    public function getContent(
        ModelProduct $product,
        string $fieldName
    ): string;
}
