<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\Adapter;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
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
     * @var \Magento\Elasticsearch\Model\Adapter\DocumentDataMapper
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

        $this->model = new DocumentDataMapper(
            $this->builderMock,
            $this->attributeContainerMock,
            $this->fieldMapperMock,
            $this->dateTimeMock,
            $this->localeDateMock,
            $this->scopeConfigMock,
            $this->storeManagerMock
        );
    }

    /**
     * Tests modules data returns array
     *
     * @dataProvider mapProvider
     * @param array $productData
     * @param int $productId
     * @param int $storeId
     * @param array $productPriceIndexData
     * @param array $productCategoryIndexData
     *
     * @return array
     */
    public function testGetMap($productData, $productId, $storeId, $productPriceData, $productCategoryData, $emptyData)
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
        $this->dateTimeMock->expects($this->any())->method('isEmptyDate')->will(
            $this->returnValue($emptyData)
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

        $store = $this->getMockBuilder('\Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->setMethods(['getWebsiteId'])
            ->getMock();

        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($store);
        $store->expects($this->once())->method('getWebsiteId')->willReturn('website_id');

        $this->assertInternalType(
            'array',
            $this->model->map($productData, $productId, $storeId, $productPriceData, $productCategoryData)
        );
    }

    /**
     * @return array
     */
    public static function mapProvider()
    {
        return [
            [
                ['price'=>'11','created_at'=>'00-00-00 00:00:00'],
                '1',
                '1',
                ['1'=>['11','11','11','11']],
                ['1' => ['2','1']],
                true
            ],
            [
                ['price'=>'11','created_at'=>'1995-12-31 23:59:59','options'=>['value1','value2']],
                '1',
                '1',
                ['1'=>['11','11','11','11']],
                ['1' => ['2','1']],
                false
            ]
        ];
    }
}
