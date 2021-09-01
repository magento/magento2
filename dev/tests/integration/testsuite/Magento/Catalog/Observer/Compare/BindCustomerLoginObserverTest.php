<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Observer\Compare;

use Magento\Catalog\Model\Product\Compare\ListCompareFactory;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Visitor;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Checks customer and visitor compare list merging after customer login
 *
 * @see \Magento\Catalog\Observer\Compare\BindCustomerLoginObserver
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 */
class BindCustomerLoginObserverTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Session */
    private $session;

    /** @var Visitor */
    private $visitor;

    /** @var ListCompareFactory */
    private $listCompareFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->session = $this->objectManager->get(Session::class);
        $this->visitor = $this->objectManager->get(Visitor::class);
        $this->listCompareFactory = $this->objectManager->get(ListCompareFactory::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->session->logout();
        $this->visitor->setId(null);

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/visitor_compare_list.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testExecute(): void
    {
        $this->visitor->setId(123);
        $this->session->loginById(1);
        $this->assertCustomerItems(1, ['simple']);
        $this->assertVisitorItems(123, []);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/customer_compare_list_with_simple_product.php
     * @magentoDataFixture Magento/Catalog/_files/product_in_compare_list_with_customer.php
     * @magentoDataFixture Magento/Catalog/_files/visitor_compare_list.php
     *
     * @return void
     */
    public function testExecuteWithSameProducts(): void
    {
        $this->visitor->setId(123);
        $this->session->loginById(1);
        $this->assertCustomerItems(1, ['simple', 'simple2']);
        $this->assertVisitorItems(123, []);
    }

    /**
     * Check customer compare items
     *
     * @param int $customerId
     * @param array $expectedProductSkus
     * @return void
     */
    private function assertCustomerItems(int $customerId, array $expectedProductSkus): void
    {
        $collection = $this->listCompareFactory->create()->getItemCollection()->useProductItem()
            ->setCustomerId($customerId);
        $this->checkCollection($collection, $expectedProductSkus);
    }

    /**
     * Checks visitor compare items
     *
     * @param int $visitorId
     * @param array $expectedProductSkus
     * @return void
     */
    private function assertVisitorItems(int $visitorId, array $expectedProductSkus): void
    {
        $collection = $this->listCompareFactory->create()->getItemCollection()->useProductItem()
            ->setVisitorId($visitorId);
        $collection->addFieldToFilter('customer_id', ['null' => true]);
        $this->checkCollection($collection, $expectedProductSkus);
    }

    /**
     * Check collection
     *
     * @param AbstractCollection $collection
     * @param array $expectedSkus
     * @return void
     */
    private function checkCollection(AbstractCollection $collection, array $expectedSkus): void
    {
        $this->assertCount(count($expectedSkus), $collection);
        foreach ($expectedSkus as $expectedSku) {
            $this->assertNotNull($collection->getItemByColumnValue('sku', $expectedSku));
        }
    }
}
