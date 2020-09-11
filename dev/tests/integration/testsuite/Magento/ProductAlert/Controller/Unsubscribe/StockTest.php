<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductAlert\Controller\Unsubscribe;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Customer\Model\Session;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Test for Magento\ProductAlert\Controller\Unsubscribe\Stock class.
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation  enabled
 */
class StockTest extends AbstractController
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * Connection adapter
     *
     * @var AdapterInterface
     */
    protected $connectionMock;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->customerSession = $this->objectManager->get(Session::class);
        $this->resource = $this->objectManager->get(ResourceConnection::class);
        $this->connectionMock = $this->resource->getConnection();
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
    }

    /**
     * @magentoAppArea     frontend
     * @magentoDataFixture Magento/Catalog/_files/product_simple_out_of_stock.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/ProductAlert/_files/customer_unsubscribe_stock.php
     */
    public function testUnsubscribeStockNotification()
    {
        $customerId = 1;
        $productId = $this->productRepository->get('simple-out-of-stock')->getId();

        $this->customerSession->setCustomerId($customerId);

        $this->getRequest()->setPostValue('product', $productId)->setMethod('POST');
        $this->dispatch('productalert/unsubscribe/stock');

        $select = $this->connectionMock->select()->from($this->resource->getTableName('product_alert_stock'))
                                       ->where('`product_id` LIKE ?', $productId);
        $result = $this->connectionMock->fetchAll($select);
        $this->assertCount(0, $result);
    }
}
