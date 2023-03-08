<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;

class ConfigurableOptionsStatusFilter implements ConfigurableOptionsFilterInterface
{
    /**
     * @inheritdoc
     */
    public function filter(ProductInterface $parentProduct, array $childProducts): array
    {
        $result = [];
        foreach ($childProducts as $childProduct) {
            if ((int) $childProduct->getStatus() === Status::STATUS_ENABLED) {
                $result[] = $childProduct;
            }
        }

        return $result;
    }
}
