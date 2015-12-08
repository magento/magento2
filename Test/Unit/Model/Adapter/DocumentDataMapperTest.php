<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\Adapter;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Elasticsearch\Model\Adapter\Container\Attribute as AttributeContainer;
use Magento\Elasticsearch\Model\Adapter\Document\Builder;
use Magento\Elasticsearch\Model\Adapter\FieldMapper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Elasticsearch\Model\Adapter\DocumentDataMapper;

/**
 * Class DocumentDataMapperTest
 */
class DocumentDataMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DocumentDataMapper
     */
    protected $model;

    /**
     * @var Builder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $builderMock;

    /**
     * @var AttributeContainer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeContainerMock;

    /**
     * @var Index|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceIndex;

    /**
     * @var FieldMapper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fieldMapperMock;

    /**
     * @var DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTimeMock;

    /**
     * @var TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localeDateMock;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * Set up test environment.
     */
    protected function setUp()
    {
        $this->builderMock = $this->getMockBuilder('Magento\Elasticsearch\Model\Adapter\Document\Builder')
            ->setMethods(['addField', 'addFields', 'build'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeContainerMock = $this->getMockBuilder('Magento\Elasticsearch\Model\Adapter\Container\Attribute')
            ->setMethods(['getAttribute', 'setStoreId', 'getBackendType', 'getFrontendInput'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->resourceIndex = $this->getMockBuilder('Magento\Elasticsearch\Model\ResourceModel\Index')
            ->disableOriginalConstructor()
            ->setMethods([
                'getPriceIndexData',
                'getFullCategoryProductIndexData',
                'getFullProductIndexData',
            ])
            ->getMock();
        $this->resourceIndex->expects($this->any())
            ->method('getPriceIndexData')
            ->willReturn([]);
        $this->resourceIndex->expects($this->any())
            ->method('getFullCategoryProductIndexData')
            ->willReturn([]);

        $this->fieldMapperMock = $this->getMockBuilder('Magento\Elasticsearch\Model\Adapter\FieldMapper')
            ->setMethods(['getFieldName'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->dateTimeMock = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime')
            ->setMethods(['isEmptyDate', 'setTimezone', 'format'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->localeDateMock = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\TimezoneInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfigMock = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManagerHelper($this);
        $this->model = $objectManager->getObject(
            '\Magento\Elasticsearch\Model\Adapter\DocumentDataMapper',
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
     *
     * @return array
     */
    public function testGetMap($productId, $productData, $storeId)
    {
        $this->attributeContainerMock->expects($this->any())->method('getAttribute')->will(
            $this->returnValue($this->attributeContainerMock)
        );
        $this->attributeContainerMock->expects($this->any())->method('setStoreId')->will(
            $this->returnValue($this->attributeContainerMock)
        );
        $this->attributeContainerMock->expects($this->any())->method('getBackendType')->will(
            $this->returnValue('datetime')
        );
        $this->attributeContainerMock->expects($this->any())->method('getFrontendInput')->will(
            $this->returnValue('date')
        );
        $this->scopeConfigMock->expects($this->any())->method('getValue')->will(
            $this->returnValue('Europe/London')
        );
        $this->builderMock->expects($this->any())->method('addField')->will(
            $this->returnValue([])
        );
        $this->builderMock->expects($this->any())->method('addFields')->will(
            $this->returnValue([])
        );
        $this->builderMock->expects($this->any())->method('build')->will(
            $this->returnValue([])
        );

        $data = [$productId => $productData];
        $this->resourceIndex->expects($this->once())
            ->method('getFullProductIndexData')
            ->willReturn($data);

        $this->assertInternalType(
            'array',
            $this->model->map($productId, $productData, $storeId)
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
                '1'
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
                '1'
            ],
            [
                '1',
                ['image'=>'11','created_at'=>'00-00-00 00:00:00'],
                '1'
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
                        ]
                    ,
                    'created_at'=>'00-00-00 00:00:00'
                ],
                '1'
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
                        ]
                    ,
                    'created_at'=>'00-00-00 00:00:00'
                ],
                '1'
            ],
            [
                '1',
                ['quantity_and_stock_status'=>'11','created_at'=>'00-00-00 00:00:00'],
                '1'
            ],
            [
                '1',
                ['price'=>'11','created_at'=>'1995-12-31 23:59:59','options'=>['value1','value2']],
                '1'
            ]
        ];
    }
}
