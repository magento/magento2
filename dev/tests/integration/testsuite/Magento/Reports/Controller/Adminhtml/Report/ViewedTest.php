<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Reports\Controller\Adminhtml\Report;

use Magento\Backend\Block\Dashboard\Tab\Products\Viewed as ViewedProductsTabBlock;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap as BootstrapHelper;
use function PHPUnit\Framework\assertEquals;

/**
 * @magentoAppArea frontend
 */
class ViewedTest extends \Magento\TestFramework\TestCase\AbstractController
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
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var EventManager
     */
    private $eventManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = BootstrapHelper::getObjectManager();
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->productRepository = $this->objectManager->get(ProductRepository::class);
        $this->eventManager = $this->objectManager->get(EventManager::class);
    }

    /**
     * Assert viewed product in reports.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/reports/options/enabled 1
     */
    public function testGetPreparedCollectionProductPrice()
    {
        $sku = 'simple';
        $product = $this->productRepository->get($sku);
        $this->getRequest()->setMethod(Http::METHOD_POST)->setPostValue('product_id', $product->getId());
        $this->dispatch('reports/report_product/view');

        /** @var ViewedProductsTabBlock $viewedProductsTabBlock */
        $viewedProductsTabBlock = $this->layout->createBlock(ViewedProductsTabBlock::class);

        $collection = $viewedProductsTabBlock->getPreparedCollection();

        $this->assertEquals(
            10,
            $collection->getFirstItem()->getDataByKey('price')
        );
    }
}
