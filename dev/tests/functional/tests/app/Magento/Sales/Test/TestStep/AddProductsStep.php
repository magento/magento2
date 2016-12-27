<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Add Products Step.
 */
class AddProductsStep implements TestStepInterface
{
    /**
     * Sales order create index page.
     *
     * @var OrderCreateIndex
     */
    private $orderCreateIndex;

    /**
     * Array products.
     *
     * @var array
     */
    private $products;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * @param OrderCreateIndex $orderCreateIndex
     * @param FixtureFactory $fixtureFactory
     * @param array $products
     */
    public function __construct(
        OrderCreateIndex $orderCreateIndex,
        FixtureFactory $fixtureFactory,
        array $products
    ) {
        $this->orderCreateIndex = $orderCreateIndex;
        $this->products = $products;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Add product to sales.
     *
     * @return array
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

        $cart['data']['items'] = ['products' => $this->products];
        return ['cart' => $this->fixtureFactory->createByCode('cart', $cart)];
    }
}
