<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api;

use Magento\Framework\Api\ExtensionAttribute\Config\Converter;
use Magento\Framework\Api\ExtensionAttribute\Config\Reader;
use Magento\Framework\Api\ExtensionAttribute\JoinData;
use Magento\Framework\Api\ExtensionAttribute\JoinDataFactory;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\App\Resource;

class ExtensionAttributesFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Api\ExtensionAttributesFactory */
    private $factory;

    /**
     * @var Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * @var JoinDataFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionAttributeJoinDataFactory;

    /**
     * @var TypeProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $typeProcessor;

    /**
     * @var AppResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $appResource;

    protected function setUp()
    {
        $this->config = $this->getMockBuilder('Magento\Framework\Api\ExtensionAttribute\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->extensionAttributeJoinDataFactory = $this
            ->getMockBuilder('Magento\Framework\Api\ExtensionAttribute\JoinDataFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->extensionAttributeJoinDataFactory = $this
            ->getMockBuilder('Magento\Framework\Api\ExtensionAttribute\JoinDataFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->typeProcessor = $this->getMockBuilder('Magento\Framework\Reflection\TypeProcessor')
            ->disableOriginalConstructor()
            ->getMock();

        $autoloadWrapper = \Magento\Framework\Autoload\AutoloaderRegistry::getAutoloader();
        $autoloadWrapper->addPsr4('Magento\\Wonderland\\', realpath(__DIR__ . '/_files/Magento/Wonderland'));
        /** @var \Magento\Framework\ObjectManagerInterface */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->appResource = $objectManager->get('Magento\Framework\App\Resource');

        $this->factory = $objectManager->create(
            'Magento\Framework\Api\ExtensionAttributesFactory',
            [
                'objectManager' => $objectManager,
                'config' => $this->config,
                'extensionAttributeJoinDataFactory' => $this->extensionAttributeJoinDataFactory,
                'typeProcessor' => $this->typeProcessor
            ]
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testCreateThrowExceptionIfInterfaceNotImplemented()
    {
        $this->factory->create('Magento\Framework\Api\ExtensionAttributesFactoryTest');
    }

    /**
     * @expectedException \LogicException
     */
    public function testCreateThrowExceptionIfInterfaceNotOverridden()
    {
        $this->factory->create('\Magento\Wonderland\Model\Data\FakeExtensibleOne');
    }

    /**
     * @expectedException \LogicException
     */
    public function testCreateThrowExceptionIfReturnIsIncorrect()
    {
        $this->factory->create('\Magento\Wonderland\Model\Data\FakeExtensibleTwo');
    }

    public function testCreate()
    {
        $this->assertInstanceOf(
            'Magento\Wonderland\Api\Data\FakeRegionExtension',
            $this->factory->create('Magento\Wonderland\Model\Data\FakeRegion')
        );
    }

    /**
     * Test the processing of the join config for a particular type
     */
    public function testProcess()
    {
        $this->config->expects($this->once())
            ->method('get')
            ->will($this->returnValue($this->getConfig()));

        $collection = $this->getMockBuilder('Magento\Framework\Data\Collection\AbstractDb')
            ->setMethods(['joinExtensionAttribute'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $extensionAttributeJoinData = new JoinData();
        $this->extensionAttributeJoinDataFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($extensionAttributeJoinData);

        $collection->expects($this->once())->method('joinExtensionAttribute')->with($extensionAttributeJoinData);

        $this->factory->process($collection, 'Magento\Catalog\Api\Data\ProductInterface');
        $expectedTableName = 'reviews';
        $this->assertEquals($expectedTableName, $extensionAttributeJoinData->getReferenceTable());
        $this->assertEquals('extension_attribute_review_id', $extensionAttributeJoinData->getReferenceTableAlias());
        $this->assertEquals('product_id', $extensionAttributeJoinData->getReferenceField());
        $this->assertEquals('id', $extensionAttributeJoinData->getJoinField());
        $this->assertEquals(['review_id'], $extensionAttributeJoinData->getSelectFields());
    }

    private function getConfig()
    {
        return [
            'Magento\Catalog\Api\Data\ProductInterface' => [
                'review_id' => [
                    Converter::DATA_TYPE => 'string',
                    Converter::RESOURCE_PERMISSIONS => [],
                    Converter::JOIN_DIRECTIVE => [
                        Converter::JOIN_REFERENCE_TABLE => "reviews",
                        Converter::JOIN_REFERENCE_FIELD => "product_id",
                        Converter::JOIN_SELECT_FIELDS => [
                            [
                                Converter::JOIN_SELECT_FIELD => "review_id",
                            ],
                        ],
                        Converter::JOIN_JOIN_ON_FIELD => "id",
                    ],
                ],
            ],
            'Magento\Customer\Api\Data\CustomerInterface' => [
                'library_card_id' => [
                    Converter::DATA_TYPE => 'string',
                    Converter::RESOURCE_PERMISSIONS => [],
                    Converter::JOIN_DIRECTIVE => [
                        Converter::JOIN_REFERENCE_TABLE => "library_account",
                        Converter::JOIN_SELECT_FIELDS => [
                            [
                                Converter::JOIN_SELECT_FIELD => "library_card_id",
                            ],
                        ],
                        Converter::JOIN_JOIN_ON_FIELD => "customer_id",
                    ],
                ],
                'reviews' => [
                    Converter::DATA_TYPE => 'Magento\Reviews\Api\Data\Reviews[]',
                    Converter::RESOURCE_PERMISSIONS => [],
                    Converter::JOIN_DIRECTIVE => [
                        Converter::JOIN_REFERENCE_TABLE => "reviews",
                        Converter::JOIN_SELECT_FIELDS => [
                            [
                                Converter::JOIN_SELECT_FIELD => "comment",
                            ],
                            [
                                Converter::JOIN_SELECT_FIELD => "rating",
                            ],
                        ],
                        Converter::JOIN_JOIN_ON_FIELD => "customer_id",
                    ],
                ],
            ],
        ];
    }

    public function testProcessSqlSelectVerification()
    {
        /** @var \Magento\Framework\ObjectManagerInterface */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Framework\Api\ExtensionAttribute\Config $config */
        $config = $objectManager->get('Magento\Framework\Api\ExtensionAttribute\Config');
        $config->reset();

        $extensionConfigFileResolverMock = $this->getMockBuilder('Magento\Framework\Config\FileResolverInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $extensionConfigFilePath = __DIR__ . '/_files/extension_attributes.xml';
        $extensionConfigFileContent = file_get_contents($extensionConfigFilePath);
        $extensionConfigFileResolverMock->expects($this->any())
            ->method('get')
            ->willReturn([$extensionConfigFilePath => $extensionConfigFileContent]);
        $configReader = $objectManager->create(
            'Magento\Framework\Api\ExtensionAttribute\Config\Reader',
            ['fileResolver' => $extensionConfigFileResolverMock]
        );
        /** @var \Magento\Framework\Api\ExtensionAttribute\Config $config */
        $config = $objectManager->create(
            'Magento\Framework\Api\ExtensionAttribute\Config',
            ['reader' => $configReader]
        );
        /** @var \Magento\Framework\Api\ExtensionAttributesFactory $extensionAttributesFactory */
        $extensionAttributesFactory = $objectManager->create(
            'Magento\Framework\Api\ExtensionAttributesFactory',
            ['config' => $config]
        );
        $productClassName = 'Magento\Catalog\Model\Product';
        /** @var \Magento\Catalog\Model\Resource\Product\Collection $collection */
        $collection = $objectManager->create('Magento\Catalog\Model\Resource\Product\Collection');

        $extensionAttributesFactory->process($collection, $productClassName);
        $config->reset();

        $catalogProductEntity = $this->appResource->getTableName('catalog_product_entity');
        $catalogInventoryStockItem = $this->appResource->getTableName('cataloginventory_stock_item');
        $reviews = $this->appResource->getTableName('reviews');
        $expectedSql = <<<EXPECTED_SQL
SELECT `e`.*,
     `extension_attribute_stock_item`.`qty` AS `extension_attribute_stock_item_qty`,
     `extension_attribute_reviews`.`comment` AS `extension_attribute_reviews_comment`,
     `extension_attribute_reviews`.`rating` AS `extension_attribute_reviews_rating`,
     `extension_attribute_reviews`.`date` AS `extension_attribute_reviews_date` FROM `$catalogProductEntity` AS `e`
 LEFT JOIN `$catalogInventoryStockItem` AS `extension_attribute_stock_item` ON e.id = extension_attribute_stock_item.id
 LEFT JOIN `$reviews` AS `extension_attribute_reviews` ON e.id = extension_attribute_reviews.product_id
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

    public function testCreateWithLogicException()
    {
        $this->setExpectedException(
            'LogicException',
            "Class 'Magento\\Framework\\Api\\ExtensionAttributesFactoryTest' must implement an interface, "
            . "which extends from 'Magento\\Framework\\Api\\ExtensibleDataInterface'"
        );
        $this->factory->create(get_class($this));
    }
}
