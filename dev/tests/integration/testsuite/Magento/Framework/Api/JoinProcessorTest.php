<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api;

class JoinProcessorTest extends \PHPUnit_Framework_TestCase
{
    // TODO: Cover LogicException case in \Magento\Framework\Api\ExtensionAttributesFactory::populateExtensionAttributes

    public function testProcess()
    {
        /** @var \Magento\Framework\ObjectManagerInterface */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $extensionConfigFileResolverMock = $this->getMockBuilder('Magento\Framework\Config\FileResolverInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $extensionConfigFilePath = __DIR__ . '/_files/extension_attributes.xml';
        $extensionConfigFileContent = file_get_contents($extensionConfigFilePath);
        $extensionConfigFileResolverMock->expects($this->any())
            ->method('get')
            ->willReturn([$extensionConfigFilePath => $extensionConfigFileContent]);
        $configReader = $objectManager->create(
            'Magento\Framework\Api\Config\Reader',
            ['fileResolver' => $extensionConfigFileResolverMock]
        );
        /** @var \Magento\Framework\Api\ExtensionAttributesFactory $extensionAttributesFactory */
        $extensionAttributesFactory = $objectManager->create(
            'Magento\Framework\Api\ExtensionAttributesFactory',
            ['configReader' => $configReader]
        );
        $productClassName = 'Magento\Catalog\Model\Product';
        /** @var \Magento\Catalog\Model\Resource\Product\Collection $collection */
        $collection = $objectManager->create('Magento\Catalog\Model\Resource\Product\Collection');

        $extensionAttributesFactory->process($collection, $productClassName);

        $expectedSql = <<<EXPECTED_SQL
SELECT `e`.*,
     `extension_attribute_stock_item`.`qty` AS `extension_attribute_stock_item_qty`,
     `extension_attribute_reviews`.`comment` AS `extension_attribute_reviews_comment`,
     `extension_attribute_reviews`.`rating` AS `extension_attribute_reviews_rating`,
     `extension_attribute_reviews`.`date` AS `extension_attribute_reviews_date` FROM `catalog_product_entity` AS `e`
 LEFT JOIN `cataloginventory_stock_item` AS `extension_attribute_stock_item` ON e.id = extension_attribute_stock_item.id
 LEFT JOIN `reviews` AS `extension_attribute_reviews` ON e.id = extension_attribute_reviews.product_id
EXPECTED_SQL;
        $resultSql = $collection->getSelectSql(true);
        $formattedResultSql = str_replace(',', ",\n    ", $resultSql);
        $this->assertEquals($expectedSql, $formattedResultSql);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products.php
     */
    public function testGetListWithExtensionAttributesAbstractModel()
    {
        $firstProductId = 1;
        $firstProductQty = 11;
        $secondProductId = 2;
        $secondProductQty = 22;
        /** @var \Magento\Framework\ObjectManagerInterface */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface');
        /** @var \Magento\CatalogInventory\Api\StockItemRepositoryInterface $stockItemRepository */
        $stockItemRepository = $objectManager->get('Magento\CatalogInventory\Api\StockItemRepositoryInterface');

        /** Prepare stock items */
        $firstStockItem = $productRepository->getById($firstProductId)->getExtensionAttributes()->getStockItem();
        $firstStockItem->setQty($firstProductQty);
        $stockItemRepository->save($firstStockItem);
        $this->assertEquals(
            $firstProductQty,
            $productRepository->getById($firstProductId)->getExtensionAttributes()->getStockItem()->getQty(),
            'Precondition failed.'
        );
        $secondStockItem = $productRepository->getById($secondProductId)->getExtensionAttributes()->getStockItem();
        $secondStockItem->setQty($secondProductQty);
        $stockItemRepository->save($secondStockItem);
        $this->assertEquals(
            $secondProductQty,
            $productRepository->getById($secondProductId)->getExtensionAttributes()->getStockItem()->getQty(),
            'Precondition failed.'
        );

        /** @var \Magento\Framework\Api\Search\FilterGroup $searchCriteriaGroup */
        $searchCriteriaGroup = $objectManager->create('Magento\Framework\Api\Search\FilterGroup');
        /** @var \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria */
        $searchCriteria = $objectManager->create('Magento\Framework\Api\SearchCriteriaInterface');
        $searchCriteria->setFilterGroups([$searchCriteriaGroup]);
        $products = $productRepository->getList($searchCriteria)->getItems();

        /** Ensure that simple extension attributes were populated correctly */
        $this->assertEquals(
            $firstProductQty,
            $products[$firstProductId]->getExtensionAttributes()->getTestStockItemQty()
        );
        $this->assertEquals(
            $secondProductQty,
            $products[$secondProductId]->getExtensionAttributes()->getTestStockItemQty()
        );

        /** Check population of complex extension attributes */
        $this->assertEquals(
            $firstProductQty,
            $products[$firstProductId]->getExtensionAttributes()->getTestStockItem()->getQty()
        );
        $this->assertNotEmpty($products[$firstProductId]->getExtensionAttributes()->getTestStockItem()->getItemId());

        $this->assertArrayNotHasKey(
            'extension_attribute_test_stock_item_qty_qty',
            $products[$firstProductId]->getData(),
            "Selected extension field should be unset after it is added to extension attributes object."
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_group.php
     */
    public function testGetListWithExtensionAttributesAbstractObject()
    {
        $customerId = 1;
        $customerGroupName = 'General';
        $taxClassId = 3;
        /** @var \Magento\Framework\ObjectManagerInterface */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
        $customerRepository = $objectManager->get('Magento\Customer\Api\CustomerRepositoryInterface');
        /** @var \Magento\Framework\Api\Search\FilterGroup $searchCriteriaGroup */
        $searchCriteriaGroup = $objectManager->create('Magento\Framework\Api\Search\FilterGroup');
        /** @var \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria */
        $searchCriteria = $objectManager->create('Magento\Framework\Api\SearchCriteriaInterface');
        $searchCriteria->setFilterGroups([$searchCriteriaGroup]);
        $customers = $customerRepository->getList($searchCriteria)->getItems();

        /** Ensure that simple extension attributes were populated correctly */
        $customer = $customers[0];
        $this->assertEquals($customerId, $customer->getId(), 'Precondition failed');
        $this->assertEquals($customerGroupName, $customer->getExtensionAttributes()->getTestGroupCode());

        /** Check population of complex extension attributes */
        $this->assertEquals($taxClassId, $customer->getExtensionAttributes()->getTestGroup()->getTaxClassId());
        $this->assertEquals($customerGroupName, $customer->getExtensionAttributes()->getTestGroup()->getCode());
    }
}
