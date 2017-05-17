<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventoryConfigurableProduct\Test\Unit\Plugin;

use Magento\CatalogInventory\Api\StockStatusCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\CatalogInventoryConfigurableProduct\Plugin\GetInStockAttributeOptionsPlugin;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\CatalogInventory\Api\StockStatusCriteriaInterface;
use Magento\ConfigurableProduct\Model\AttributeOptionProviderInterface;
use Magento\CatalogInventory\Api\Data\StockStatusCollectionInterface;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;

class GetInStockAttributeOptionsPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GetInStockAttributeOptionsPlugin
     */
    private $plugin;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var StockStatusCriteriaInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stockStatusCriteriaFactory;

    /**
     * @var StockStatusCriteriaInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stockStatusCriteria;

    /**
     * @var StockStatusRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stockStatusRepository;

    /**
     * @var AttributeOptionProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeOptionProvider;

    /**
     * @var StockStatusCollectionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stockStatusCollection;

    protected function setUp()
    {
        $this->stockStatusCriteriaFactory = $this->getMockBuilder(StockStatusCriteriaInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMockForAbstractClass();
        $this->stockStatusRepository = $this->getMockBuilder(StockStatusRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->attributeOptionProvider = $this->getMockBuilder(AttributeOptionProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->stockStatusCriteria = $this->getMockBuilder(StockStatusCriteriaInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->stockStatusCollection = $this->getMockBuilder(StockStatusCollectionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getItems'])
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->plugin = $this->objectManagerHelper->getObject(
            GetInStockAttributeOptionsPlugin::class,
            [
                'stockStatusCriteriaFactory' => $this->stockStatusCriteriaFactory,
                'stockStatusRepository' => $this->stockStatusRepository,
            ]
        );
    }

    /**
     * @param array $options
     * @dataProvider testOptionsDataProvider
     */
    public function testGetInStockAttributeOptions(array $options)
    {
        $expectedOptions = [
            [
                'sku' => 'Configurable1-White',
                'product_id' => 4,
                'attribute_code' => 'color',
                'value_index' => '14',
                'option_title' => 'White'
            ],
            [
                'sku' => 'Configurable1-Red',
                'product_id' => 4,
                'attribute_code' => 'color',
                'value_index' => '15',
                'option_title' => 'Red'
            ]
        ];
        $status1 = $this->getMockBuilder(StockStatusInterface::class)
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $status2 = $this->getMockBuilder(StockStatusInterface::class)
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $statuses = [$status1, $status2];
        $this->stockStatusCriteriaFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->stockStatusCriteria);
        $this->stockStatusCriteria->expects($this->atLeastOnce())
            ->method('addFilter')
            ->willReturnSelf();
        $this->stockStatusRepository->expects($this->once())
            ->method('getList')
            ->willReturn($this->stockStatusCollection);
        $this->stockStatusCollection->expects($this->once())
            ->method('getTotalCount')
            ->willReturn(2);
        $this->stockStatusCollection->expects($this->any())
            ->method('getItems')
            ->willReturn($statuses);
        $status1->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturn('Configurable1-White');
        $status2->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturn('Configurable1-Red');

        $this->assertEquals(
            $expectedOptions,
            $this->plugin->afterGetAttributeOptions($this->attributeOptionProvider, $options)
        );
    }

    /**
     * @param array $options
     * @dataProvider testOptionsDataProvider
     */
    public function testGetInStockAttributeOptionsWithAllOutOfStock(array $options)
    {
        $expectedOptions = [];
        $this->stockStatusCriteriaFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->stockStatusCriteria);
        $this->stockStatusCriteria->expects($this->atLeastOnce())
            ->method('addFilter')
            ->willReturnSelf();
        $this->stockStatusRepository->expects($this->once())
            ->method('getList')
            ->willReturn($this->stockStatusCollection);
        $this->stockStatusCollection->expects($this->once())
            ->method('getTotalCount')
            ->willReturn(0);

        $this->assertEquals(
            $expectedOptions,
            $this->plugin->afterGetAttributeOptions($this->attributeOptionProvider, $options)
        );
    }

    /**
     * @param array $options
     * @dataProvider testOptionsDataProvider
     */
    public function testGetInStockAttributeOptionsWithAllInStock(array $options)
    {
        $expectedOptions = [
            [
                'sku' => 'Configurable1-Black',
                'product_id' => 4,
                'attribute_code' => 'color',
                'value_index' => '13',
                'option_title' => 'Black'
            ],
            [
                'sku' => 'Configurable1-White',
                'product_id' => 4,
                'attribute_code' => 'color',
                'value_index' => '14',
                'option_title' => 'White'
            ],
            [
                'sku' => 'Configurable1-Red',
                'product_id' => 4,
                'attribute_code' => 'color',
                'value_index' => '15',
                'option_title' => 'Red'
            ]
        ];
        $status1 = $this->getMockBuilder(StockStatusInterface::class)
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $status2 = $this->getMockBuilder(StockStatusInterface::class)
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $status3 = $this->getMockBuilder(StockStatusInterface::class)
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $statuses = [$status1, $status2, $status3];
        $this->stockStatusCriteriaFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->stockStatusCriteria);
        $this->stockStatusCriteria->expects($this->atLeastOnce())
            ->method('addFilter')
            ->willReturnSelf();
        $this->stockStatusRepository->expects($this->once())
            ->method('getList')
            ->willReturn($this->stockStatusCollection);
        $this->stockStatusCollection->expects($this->once())
            ->method('getTotalCount')
            ->willReturn(3);
        $this->stockStatusCollection->expects($this->any())
            ->method('getItems')
            ->willReturn($statuses);
        $status1->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturn('Configurable1-Black');
        $status2->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturn('Configurable1-White');
        $status3->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturn('Configurable1-Red');

        $this->assertEquals(
            $expectedOptions,
            $this->plugin->afterGetAttributeOptions($this->attributeOptionProvider, $options)
        );
    }

    /**
     * @return array
     */
    public function testOptionsDataProvider()
    {
        return [
            [
                [
                    [
                        'sku' => 'Configurable1-Black',
                        'product_id' => 4,
                        'attribute_code' => 'color',
                        'value_index' => '13',
                        'option_title' => 'Black'
                    ],
                    [
                        'sku' => 'Configurable1-White',
                        'product_id' => 4,
                        'attribute_code' => 'color',
                        'value_index' => '14',
                        'option_title' => 'White'
                    ],
                    [
                        'sku' => 'Configurable1-Red',
                        'product_id' => 4,
                        'attribute_code' => 'color',
                        'value_index' => '15',
                        'option_title' => 'Red'
                    ]
                ]
            ]
        ];
    }
}
