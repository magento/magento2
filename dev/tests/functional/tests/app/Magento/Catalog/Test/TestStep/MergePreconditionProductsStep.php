<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Merge products.
 */
class MergePreconditionProductsStep implements TestStepInterface
{
    /**
     * Array of product entities.
     *
     * @var array
     */
    private $products;

    /**
     * Created products at __prepare method to able use them at all test variations and decrease time to create them.
     *
     * @var array
     */
    private $preconditionProducts;

    /**
     * @param array $products [optional]
     * @param array $preconditionProducts [optional]
     */
    public function __construct(array $products = [], array $preconditionProducts = [])
    {
        $this->products = $products;
        $this->preconditionProducts = $preconditionProducts;
    }

    /**
     * Merge products step.
     *
     * @return array
     */
    public function run()
    {
        $products = array_merge($this->preconditionProducts, $this->products);
        return ['products' => $products];
    }
}
