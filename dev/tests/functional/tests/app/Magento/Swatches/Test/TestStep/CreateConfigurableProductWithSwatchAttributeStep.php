<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Swatches\Test\Fixture\ConfigurableProduct;

/**
 * Update configurable product step.
 */
class CreateConfigurableProductWithSwatchAttributeStep implements TestStepInterface
{
    /**
     * @var ConfigurableProduct
     */
    private $product;

    /**
     * @constructor
     * @param ConfigurableProduct $product
     */
    public function __construct(
        ConfigurableProduct $product
    ) {
        $this->product = $product;
    }

    /**
     * Update configurable product.
     *
     * @return array
     */
    public function run()
    {
        $this->product->persist();
        return ['product' => $this->product];
    }
}
