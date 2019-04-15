<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Msrp\Pricing;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * @inheritdoc
 */
class MsrpPriceCalculator implements MsrpPriceCalculatorInterface
{
    /**
     * @var MsrpPriceCalculatorInterface[]
     */
    private $msrpPriceCalculators;

    /**
     * @param array $msrpPriceCalculators
     */
    public function __construct(array $msrpPriceCalculators)
    {
        $this->msrpPriceCalculators = $this->getMsrpPriceCalculators($msrpPriceCalculators);
    }

    /**
     * @inheritdoc
     */
    public function getMsrpPriceValue(ProductInterface $product): float
    {
        $productType = $product->getTypeId();
        if (isset($this->msrpPriceCalculators[$productType])) {
            $priceCalculator = $this->msrpPriceCalculators[$productType];
            $msrp = $priceCalculator->getMsrpPriceValue($product);
        } else {
            $msrp = (float)$product->getMsrp();
        }

        return $msrp;
    }

    /**
     * Convert the configuration of MSRP price calculators.
     *
     * @param array $msrpPriceCalculatorsConfig
     * @return array
     */
    private function getMsrpPriceCalculators(array $msrpPriceCalculatorsConfig): array
    {
        $msrpPriceCalculators = [];
        foreach ($msrpPriceCalculatorsConfig as $msrpPriceCalculator) {
            if (isset($msrpPriceCalculator['productType'], $msrpPriceCalculator['priceCalculator'])) {
                $msrpPriceCalculators[$msrpPriceCalculator['productType']] =
                    $msrpPriceCalculator['priceCalculator'];
            }
        }
        return $msrpPriceCalculators;
    }
}
