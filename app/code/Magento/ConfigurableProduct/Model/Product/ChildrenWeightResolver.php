<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Model\Product;

use Magento\Catalog\Model\Product\Type as ProductType;

class ChildrenWeightResolver
{
    /**
     * @param array $postData
     *
     * @return string
     */
    public function processProduct(array $postData): string
    {
        return isset($postData['weight']) && !empty($postData['weight'])
            ? ProductType::TYPE_SIMPLE
            : ProductType::TYPE_VIRTUAL;
    }
}
