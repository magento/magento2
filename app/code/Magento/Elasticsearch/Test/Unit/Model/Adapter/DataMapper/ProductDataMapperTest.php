<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\DataMapper;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Elasticsearch\Model\Adapter\Container\Attribute as AttributeContainer;
use Magento\Elasticsearch\Model\Adapter\Document\Builder;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Elasticsearch\Model\Adapter\DataMapper\ProductDataMapper;
use Magento\Elasticsearch\Model\ResourceModel\Index;
use Magento\AdvancedSearch\Model\ResourceModel\Index as AdvancedSearchIndex;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Class ProductDataMapperTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductDataMapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProductDataMapper
     */
    protected $model;

    /**
     * @var Builder|\PHPUnit\Framework\MockObject\MockObject
     */
    private $builderMock;

    /**
     * @var AttributeContainer|\PHPUnit\Framework\MockObject\MockObject
     */
    private $attributeContainerMock;

    /**
     * @var Attribute|\PHPUnit\Framework\MockObject\MockObject
     */
    private $attribute;

    /**
     * @var Index|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resourceIndex;

    /**
     * @var AdvancedSearchIndex|\PHPUnit\Framework\MockObject\MockObject
     */
    private $advancedSearchIndex;

    /**
     * @var FieldMapperInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $fieldMapperMock;

    /**
     * @var DateTime|\PHPUnit\Framework\MockObject\MockObject
     */
    private $dateTimeMock;

    /**
     * @var TimezoneInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $localeDateMock;

    /**
     * @var ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeConfigMock;

    /**
     * @var StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $storeManagerMock;

    /**
     * @var StoreInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $storeInterface;

    /**
     * Set up test environment.
     */
    protected function setUp(): void
    {
        $this->builderMock = $this->getMockBuilder(\Magento\Elasticsearch\Model\Adapter\Document\Builder::class)
            ->setMethods(['addField', 'addFields', 'build'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeContainerMock = $this->getMockBuilder(
            \Magento\Elasticsearch\Model\Adapter\Container\Attribute::class
        )->setMethods(['getAttribute', 'setStoreId', 'getBackendType', 'getFrontendInput'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->resourceIndex = $this->getMockBuilder(\Magento\Elasticsearch\Model\ResourceModel\Index::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getPriceIndexData',
                'getFullCategoryProductIndexData',
                'getFullProductIndexData',
            ])
            ->getMock();

        $this->fieldMapperMock = $this->getMockBuilder(\Magento\Elasticsearch\Model\Adapter\FieldMapperInterface::class)
            ->setMethods(['getFieldName', 'getAllAttributesTypes'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->dateTimeMock = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime::class)
            ->setMethods(['isEmptyDate', 'setTimezone', 'format'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->localeDateMock = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->advancedSearchIndex = $this->getMockBuilder(\Magento\AdvancedSearch\Model\ResourceModel\Index::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->attribute = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeInterface = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManagerHelper($this);
        $this->model = $objectManager->getObject(
            \Magento\Elasticsearch\Model\Adapter\DataMapper\ProductDataMapper::class,
            [
                'builder' => $this->builderMock,
                'attributeContainer' => $this->attributeContainerMock,
                'resourceIndex' => $this->resourceIndex,
                'fieldMapper' => $this->fieldMapperMock,
                'dateTime' => $this->dateTimeMock,
                'localeDate' => $this->localeDateMock,
                'scopeConfig' => $this->scopeConfigMock,
                'storeManager' => $this->storeManagerMock
            ]
        );
    }

    /**
     * Tests modules data returns array
     *
     * @dataProvider mapProvider
     * @param int $productId
     * @param array $productData
     * @param int $storeId
     * @param bool $emptyDate
     * @param string $type
     * @param string $frontendInput
     *
     * @return void
     */
    public function testGetMap($productId, $productData, $storeId, $emptyDate, $type, $frontendInput)
    {
        $this->attributeContainerMock->expects($this->any())->method('getAttribute')->willReturn(
            $this->attribute
        );
        $this->resourceIndex->expects($this->any())
            ->method('getPriceIndexData')
            ->with([1, ], 1)
            ->willReturn([
                1 => [1]
            ]);
        $this->resourceIndex->expects($this->any())
            ->method('getFullCategoryProductIndexData')
            ->willReturn([
                1 => [
                    0 => [
                        'id' => 2,
                        'name' => 'Default Category',
                        'position' => '1',
                    ],
                    1 => [
                        'id' => 3,
                        'name' => 'Gear',
                        'position' => '1',
                    ],
                    2 => [
                        'id' => 4,
                        'name' => 'Bags',
                        'position' => '1',
                    ],
                ],
            ]);
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeInterface);
        $this->storeInterface->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn(1);
        $this->attributeContainerMock->expects($this->any())->method('setStoreId')->willReturn(
            $this->attributeContainerMock
        );
        $this->attribute->expects($this->any())->method('getBackendType')->willReturn(
            $type
        );
        $this->attribute->expects($this->any())->method('getFrontendInput')->willReturn(
            $frontendInput
        );
        $this->dateTimeMock->expects($this->any())->method('isEmptyDate')->willReturn(
            $emptyDate
        );
        $this->scopeConfigMock->expects($this->any())->method('getValue')->willReturn(
            'Europe/London'
        );
        $this->builderMock->expects($this->any())->method('addField')->willReturn(
            []
        );
        $this->builderMock->expects($this->any())->method('addFields')->willReturn(
            []
        );
        $this->builderMock->expects($this->any())->method('build')->willReturn(
            []
        );

        $this->resourceIndex->expects($this->once())
            ->method('getFullProductIndexData')
            ->willReturn($productData);

        $this->assertIsArray($this->model->map($productId, $productData, $storeId)
        );
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function mapProvider()
    {
        return [
            [
                '1',
                ['price'=>'11','created_at'=>'00-00-00 00:00:00', 'color_value'=>'11'],
                '1',
                false,
                'datetime',
                'select',
            ],
            [
                '1',
                ['price'=>'11','created_at'=>'00-00-00 00:00:00', 'color_value'=>'11'],
                '1',
                false,
                'time',
                'multiselect',
            ],
            [
                '1',
                ['price'=>'11','created_at'=>null,'color_value'=>'11', ],
                '1',
                true,
                'datetime',
                'select',
            ],
            [
                '1',
                [
                    'tier_price'=>
                        [[
                            'price_id'=>'1',
                            'website_id'=>'1',
                            'all_groups'=>'1',
                            'cust_group'=>'1',
                            'price_qty'=>'1',
                            'website_price'=>'1',
                            'price'=>'1'
                        ]],
                        'created_at'=>'00-00-00 00:00:00'
                ],
                '1',
                false,
                'string',
                'select',
            ],
            [
                '1',
                ['image'=>'11','created_at'=>'00-00-00 00:00:00'],
                '1',
                false,
                'string',
                'select',
            ],
            [
                '1',
                [
                    'image' => '1',
                    'small_image' => '1',
                    'thumbnail' => '1',
                    'swatch_image' => '1',
                    'media_gallery'=>
                        [
                            'images' =>
                                [[
                                    'file'=>'1',
                                    'media_type'=>'image',
                                    'position'=>'1',
                                    'disabled'=>'1',
                                    'label'=>'1',
                                    'title'=>'1',
                                    'base_image'=>'1',
                                    'small_image'=>'1',
                                    'thumbnail'=>'1',
                                    'swatch_image'=>'1'
                                ]]
                        ],
                        'created_at'=>'00-00-00 00:00:00'
                ],
                '1',
                false,
                'string',
                'select',
            ],
            [
                '1',
                [
                    'image' => '1',
                    'small_image' => '1',
                    'thumbnail' => '1',
                    'swatch_image' => '1',
                    'media_gallery'=>
                        [
                            'images' =>
                                [[
                                    'file'=>'1',
                                    'media_type'=>'video',
                                    'position'=>'1',
                                    'disabled'=>'1',
                                    'label'=>'1',
                                    'title'=>'1',
                                    'base_image'=>'1',
                                    'small_image'=>'1',
                                    'thumbnail'=>'1',
                                    'swatch_image'=>'1',
                                    'video_title'=>'1',
                                    'video_url'=>'1',
                                    'video_description'=>'1',
                                    'video_metadata'=>'1',
                                    'video_provider'=>'1'
                                ]]
                        ],
                        'created_at'=>'00-00-00 00:00:00'
                ],
                '1',
                false,
                'string',
                'select',
            ],
            [
                '1',
                ['quantity_and_stock_status'=>'11','created_at'=>'00-00-00 00:00:00'],
                '1',
                false,
                'string',
                'select',
            ],
            [
                '1',
                ['quantity_and_stock_status'=>['is_in_stock' => '1', 'qty' => '12'],'created_at'=>'00-00-00 00:00:00'],
                '1',
                false,
                'string',
                'select',
            ],
            [
                '1',
                ['price'=>'11','created_at'=>'1995-12-31 23:59:59','options'=>['value1','value2']],
                '1',
                false,
                'string',
                'select',
            ],
        ];
    }
}
