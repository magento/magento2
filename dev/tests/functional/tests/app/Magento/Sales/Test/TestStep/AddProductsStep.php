<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Class AddProductsStep
 * Add Products Step
 */
class AddProductsStep implements TestStepInterface
{
    /**
     * Sales order create index page
     *
     * @var OrderCreateIndex
     */
    protected $orderCreateIndex;

    /**
     * Array products
     *
     * @var array
     */
    protected $products;

    /**
     * @constructor
     * @param OrderCreateIndex $orderCreateIndex
     * @param array $products
     */
    public function __construct(OrderCreateIndex $orderCreateIndex, array $products)
    {
        $this->orderCreateIndex = $orderCreateIndex;
        $this->products = $products;
    }

    /**
     * Add product to sales
     *
     * @return void
     */
    public function run()
    {
        $createBlock = $this->orderCreateIndex->getCreateBlock();
        $createBlock->getItemsBlock()->clickAddProducts();
        foreach ($this->products as $product) {
            $createBlock->getGridBlock()->searchAndSelect(['sku' => $product->getSku()]);
            $createBlock->getTemplateBlock()->waitLoader();
            if ($this->orderCreateIndex->getConfigureProductBlock()->isVisible()) {
                $this->orderCreateIndex->getConfigureProductBlock()->configProduct($product);
            }
        }
        $createBlock->addSelectedProductsToOrder();
        $createBlock->getTemplateBlock()->waitLoader();
    }
}
