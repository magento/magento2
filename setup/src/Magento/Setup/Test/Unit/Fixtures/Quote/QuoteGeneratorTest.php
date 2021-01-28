<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures\Quote;

/**
 * Test for Magento\Setup\Fixtures\Quote\QuoteGenerator class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $productRepository;

    /**
     * @var \Magento\ConfigurableProduct\Api\OptionRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $optionRepository;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $productCollectionFactory;

    /**
     * @var \Magento\ConfigurableProduct\Api\LinkManagementInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $linkManagement;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $serializer;

    /**
     * @var \Magento\Setup\Fixtures\Quote\QuoteConfiguration|\PHPUnit\Framework\MockObject\MockObject
     */
    private $config;

    /**
     * @var \Magento\Setup\Fixtures\FixtureModel|\PHPUnit\Framework\MockObject\MockObject
     */
    private $fixtureModelMock;

    /**
     * @var \Magento\Setup\Fixtures\Quote\QuoteGenerator
     */
    private $fixture;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->fixtureModelMock = $this->getMockBuilder(\Magento\Setup\Fixtures\FixtureModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->productRepository = $this->getMockBuilder(\Magento\Catalog\Api\ProductRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->optionRepository = $this->getMockBuilder(
            \Magento\ConfigurableProduct\Api\OptionRepositoryInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->productCollectionFactory = $this->getMockBuilder(
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->linkManagement = $this->getMockBuilder(\Magento\ConfigurableProduct\Api\LinkManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->serializer = $this->getMockBuilder(\Magento\Framework\Serialize\SerializerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->config = $this->getMockBuilder(\Magento\Setup\Fixtures\Quote\QuoteConfiguration::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getSimpleCountTo',
                    'getConfigurableCountTo',
                    'getBigConfigurableCountTo',
                    'getRequiredQuoteQuantity',
                    'getFixtureDataFilename',
                    'getExistsQuoteQuantity',
                ]
            )
            ->getMock();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->fixture = $objectManager->getObject(
            \Magento\Setup\Fixtures\Quote\QuoteGenerator::class,
            [
                'fixtureModel' => $this->fixtureModelMock,
                'storeManager' => $this->storeManager,
                'productRepository' => $this->productRepository,
                'optionRepository' => $this->optionRepository,
                'productCollectionFactory' => $this->productCollectionFactory,
                'linkManagement' => $this->linkManagement,
                'serializer' => $this->serializer,
                'config' => $this->config,
            ]
        );
    }

    /**
     * Test generateQuotes method.
     *
     * @return void
     */
    public function testGenerateQuotes()
    {
        $storeId = 1;
        $websiteId = 1;
        $storeGroupId = 1;
        $simpleProductIds = [1, 2];
        $configurableProductId = [3];
        $bigConfigurableProductId = [4];
        $dir = str_replace('Test/Unit/', '', dirname(__DIR__));
        $store = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $website = $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $storeGroup = $this->getMockBuilder(\Magento\Store\Api\Data\GroupInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $productCollection = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->config->expects($this->atLeastOnce())->method('getSimpleCountTo')->willReturn(2);
        $this->config->expects($this->atLeastOnce())->method('getConfigurableCountTo')->willReturn(1);
        $this->config->expects($this->atLeastOnce())->method('getBigConfigurableCountTo')->willReturn(1);
        $this->config->expects($this->atLeastOnce())->method('getRequiredQuoteQuantity')->willReturn(1);
        $this->config->expects($this->atLeastOnce())->method('getExistsQuoteQuantity')->willReturn(0);
        $this->config->expects($this->atLeastOnce())
            ->method('getFixtureDataFilename')
            ->willReturn($dir . DIRECTORY_SEPARATOR . "_files" . DIRECTORY_SEPARATOR . 'orders_fixture_data.json');
        $this->storeManager->expects($this->atLeastOnce())->method('getStores')->willReturn([$store]);
        $this->storeManager->expects($this->atLeastOnce())
            ->method('getWebsite')->with($websiteId)->willReturn($website);
        $this->storeManager->expects($this->atLeastOnce())
            ->method('getGroup')->with($storeGroupId)->willReturn($storeGroup);
        $store->expects($this->atLeastOnce())->method('getId')->willReturn($storeId);
        $store->expects($this->atLeastOnce())->method('getWebsiteId')->willReturn($websiteId);
        $store->expects($this->atLeastOnce())->method('getStoreGroupId')->willReturn($storeGroupId);
        $website->expects($this->atLeastOnce())->method('getName')->willReturn('Default');
        $store->expects($this->atLeastOnce())->method('getName')->willReturn('Default');
        $storeGroup->expects($this->atLeastOnce())->method('getName')->willReturn('Default');
        $this->storeManager->expects($this->atLeastOnce())->method('setCurrentStore')->with($storeId);
        $this->productCollectionFactory->expects($this->atLeastOnce())
            ->method('create')->willReturn($productCollection);
        $productCollection->expects($this->atLeastOnce())->method('addStoreFilter')->with(1)->willReturnSelf();
        $productCollection->expects($this->atLeastOnce())->method('addWebsiteFilter')->with(1)->willReturnSelf();
        $productCollection->expects($this->atLeastOnce())->method('getSelect')->willReturn($select);
        $select->expects($this->atLeastOnce())
            ->method('where')
            ->withConsecutive(
                [' type_id = \'simple\' '],
                [' sku NOT LIKE \'Big%\' '],
                [' type_id = \'configurable\' '],
                [' sku NOT LIKE \'Big%\' '],
                [' type_id = \'configurable\' '],
                [' sku LIKE \'Big%\' ']
            )->willReturnSelf();
        $productCollection->expects($this->atLeastOnce())
            ->method('getAllIds')
            ->withConsecutive([2], [1], [1])
            ->willReturnOnConsecutiveCalls($simpleProductIds, $configurableProductId, $bigConfigurableProductId);
        $this->prepareProducts();
        $this->mockConnection();
        $this->fixture->generateQuotes();
    }

    /**
     * Prepare products mocks.
     *
     * @return void
     */
    private function prepareProducts()
    {
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $configurableChild = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $childProduct = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $option = $this->getMockBuilder(\Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $optionValue = $this->getMockBuilder(\Magento\ConfigurableProduct\Api\Data\OptionValueInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->productRepository->expects($this->atLeastOnce())
            ->method('getById')
            ->withConsecutive([1], [2], [3], [4])
            ->willReturn($product);
        $product->expects($this->atLeastOnce())
            ->method('getSku')->willReturnOnConsecutiveCalls('sku1', 'sku2', 'sku3', 'sku3', 'sku4', 'sku4');
        $product->expects($this->atLeastOnce())
            ->method('getName')->willReturnOnConsecutiveCalls('name1', 'name2', 'name3', 'name4');
        $this->serializer->expects($this->atLeastOnce())
            ->method('serialize')
            ->willReturn('a:1:{i:10;i:1;}');
        $this->optionRepository->expects($this->atLeastOnce())
            ->method('getList')
            ->withConsecutive(['sku3'], ['sku4'])
            ->willReturn([$option]);
        $this->linkManagement->expects($this->atLeastOnce())
            ->method('getChildren')
            ->withConsecutive(['sku3'], ['sku4'])
            ->willReturn([$configurableChild]);
        $configurableChild->expects($this->atLeastOnce())
            ->method('getSku')
            ->willReturnOnConsecutiveCalls('childSku3', 'childSku3', 'childSku4', 'childSku4');
        $this->productRepository->expects($this->atLeastOnce())
            ->method('get')
            ->withConsecutive(['childSku3'], ['childSku4'])
            ->willReturn($childProduct);
        $childProduct->expects($this->atLeastOnce())->method('getId')->willReturnOnConsecutiveCalls(10, 11);
        $option->expects($this->atLeastOnce())->method('getLabel')->willReturnOnConsecutiveCalls('label3', 'label4');
        $option->expects($this->atLeastOnce())
            ->method('getAttributeId')->willReturnOnConsecutiveCalls(10, 10, 20, 20);
        $option->expects($this->atLeastOnce())->method('getValues')->willReturn([$optionValue]);
        $optionValue->expects($this->atLeastOnce())->method('getValueIndex')->willReturn(1);
        $configurableChild->expects($this->atLeastOnce())
            ->method('getName')->willReturnOnConsecutiveCalls('childName3', 'childName4');
    }

    /**
     * Mock connection to DB and queries.
     *
     * @return void
     */
    private function mockConnection()
    {
        $objectManager = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $resource = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $statement = $this->getMockBuilder(\Magento\Framework\DB\Statement\Pdo\Mysql::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fixtureModelMock->expects($this->atLeastOnce())->method('getObjectManager')->willReturn($objectManager);
        $objectManager->expects($this->atLeastOnce())
            ->method('get')
            ->willReturn($resource);
        $resource->expects($this->atLeastOnce())->method('getConnection')->willReturn($connection);
        $connection->expects($this->atLeastOnce())
            ->method('getTableName')
            ->willReturn('table_name');
        $resource->expects($this->atLeastOnce())
            ->method('getTable')
            ->willReturn('table_name');
        $connection->expects($this->atLeastOnce())
            ->method('query')
            ->willReturn($statement);
        $connection->expects($this->atLeastOnce())->method('getTransactionLevel')->willReturn(0);
        $connection->expects($this->atLeastOnce())->method('beginTransaction')->willReturnSelf();
        $statement->expects($this->atLeastOnce())->method('fetchColumn')->with(0)->willReturn(25);
    }
}
