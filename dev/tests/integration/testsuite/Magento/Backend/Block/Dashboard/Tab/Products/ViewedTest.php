<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Dashboard\Tab\Products;

use Magento\TestFramework\Helper\Bootstrap as BootstrapHelper;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Backend\Block\Dashboard\Tab\Products\Viewed as ViewedProductsTabBlock;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Catalog\Pricing\Price\FinalPrice;

/**
 * @magentoAppArea frontend
 */
class ViewedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var EventManager
     */
    private $eventManager;

    protected function setUp()
    {
        $this->objectManager = BootstrapHelper::getObjectManager();
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->eventManager = $this->objectManager->get(EventManager::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testGetPreparedCollectionProductPrice()
    {
        /** @var ViewedProductsTabBlock $viewedProductsTabBlock */
        $viewedProductsTabBlock = $this->layout->createBlock(ViewedProductsTabBlock::class);
        /** @var Product $product */
        $product = $this->objectManager->create(Product::class);
        $product->load(1);
        $this->eventManager->dispatch('catalog_controller_product_view', ['product' => $product]);

        $this->assertEquals(
            10,
            $viewedProductsTabBlock->getPreparedCollection()->getFirstItem()->getDataByKey('price')
        );
    }
}
