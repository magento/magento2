<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * Test class for \Magento\Weee\Model\Attribute\Backend\Weee\Tax
 */
namespace Magento\Weee\Test\Unit\Model\Attribute\Backend\Weee;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class TaxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
    }

    public function testGetBackendModelName()
    {
        $this->assertEquals(
            \Magento\Weee\Model\Attribute\Backend\Weee\Tax::class,
            \Magento\Weee\Model\Attribute\Backend\Weee\Tax::getBackendModelName()
        );
    }

    /**
     * @dataProvider dataProviderValidate
     * @param $data
     * @param $expected
     */
    public function testValidate($data, $expected)
    {
        $attributeMock = $this->getMockBuilder(\Magento\Eav\Model\Attribute::class)
            ->setMethods(['getName'])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeMock
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('weeeTax'));

        $modelMock = $this->getMockBuilder(\Magento\Weee\Model\Attribute\Backend\Weee\Tax::class)
            ->setMethods(['getAttribute'])
            ->disableOriginalConstructor()
            ->getMock();
        $modelMock
            ->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($attributeMock));

        $taxes = [reset($data)];
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $productMock
            ->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($taxes));

        // No exception
        $modelMock->validate($productMock);

        $taxes = $data;
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $productMock
            ->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($taxes));

        // Exception caught
        $this->setExpectedException('Exception', $expected);
        $modelMock->validate($productMock);
    }

    /**
     * @return array
     */
    public function dataProviderValidate()
    {
        return [
            'withDuplicate' => [
                'data' => [
                    ['state' => 12, 'country' => 'US', 'website_id' => '1'],
                    ['state' => 99, 'country' => 'ES', 'website_id' => '1'],
                    ['state' => 12, 'country' => 'US', 'website_id' => '1'],
                    ['state' => null, 'country' => 'ES', 'website_id' => '1']
                ],
                'expected' => 'You must set unique country-state combinations within the same fixed product tax',
                ]
        ];
    }

    public function testAfterLoad()
    {
        $data = [['website_id' => 1, 'value' => 1]];

        $attributeTaxMock = $this->getMockBuilder(\Magento\Weee\Model\ResourceModel\Attribute\Backend\Weee\Tax::class)
            ->setMethods(['loadProductData'])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeTaxMock
            ->expects($this->any())
            ->method('loadProductData')
            ->will($this->returnValue($data));

        $attributeMock = $this->getMockBuilder(\Magento\Eav\Model\Attribute::class)
            ->setMethods(['getName'])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeMock
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('weeeTax'));

        $model = $this->objectManager->getObject(
            \Magento\Weee\Model\Attribute\Backend\Weee\Tax::class,
            [
                'attributeTax' => $attributeTaxMock,
                '_attribute' => $attributeMock
            ]
        );

        $model->setAttribute($attributeMock);
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['setData'])
            ->disableOriginalConstructor()
            ->getMock();

        $model->afterLoad($productMock);
    }

    /**
     * Tests the specific method with various regions
     *
     * @param array $origData
     * @param array $currentData
     * @param array $expectedData
     * @dataProvider dataProviderAfterSaveWithRegion
     */
    public function testAfterSaveWithRegion($origData, $currentData, $expectedData)
    {
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['getOrigData', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();

        $productMock
            ->expects($this->once())
            ->method('getOrigData')
            ->will($this->returnValue($origData));
        $productMock
            ->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($currentData));

        $attributeTaxMock = $this->getMockBuilder(\Magento\Weee\Model\ResourceModel\Attribute\Backend\Weee\Tax::class)
            ->setMethods(['deleteProductData', 'insertProductData'])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeTaxMock
            ->expects($this->once())
            ->method('deleteProductData')
            ->will($this->returnValue(null));
        $attributeTaxMock
            ->expects($this->once())
            ->method('insertProductData')
            ->with($productMock, $expectedData)
            ->will($this->returnValue(null));

        $attributeMock = $this->getMockBuilder(\Magento\Eav\Model\Attribute::class)
            ->setMethods(['getName', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeMock
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('weeeTax'));
        $attributeMock
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));

        $model = $this->objectManager->getObject(
            \Magento\Weee\Model\Attribute\Backend\Weee\Tax::class,
            [
                'attributeTax' => $attributeTaxMock,
                '_attribute' => $attributeMock
            ]
        );

        $model->setAttribute($attributeMock);
        $model->afterSave($productMock);
    }

    /**
     * @return array
     */
    public function dataProviderAfterSaveWithRegion()
    {
        return [
            'withRegion' => [
                'origData' => [['state' => 12, 'country' => 'US', 'website_id' => '1']],
                'currentData' => [['state' => 12, 'country' => 'US', 'website_id' => '2', 'price' => 100]],
                'expectedData' => ['state' => 12, 'country' => 'US', 'website_id' => '2', 'value' => 100,
                                   'attribute_id' => 1]],
            'withNoRegion' => [
                'origData' => [['country' => 'US', 'website_id' => '1']],
                'currentData' => [['country' => 'US', 'website_id' => '2', 'price' => 100]],
                'expectedData' => ['state' => 0, 'country' => 'US', 'website_id' => '2', 'value' => 100,
                                   'attribute_id' => 1]]
        ];
    }

    public function testAfterDelete()
    {
        $attributeTaxMock = $this->getMockBuilder(\Magento\Weee\Model\ResourceModel\Attribute\Backend\Weee\Tax::class)
            ->setMethods(['deleteProductData'])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeTaxMock
            ->expects($this->once())
            ->method('deleteProductData')
            ->with(null, null)
            ->will($this->returnValue(null));

        $model = $this->objectManager->getObject(
            \Magento\Weee\Model\Attribute\Backend\Weee\Tax::class,
            [
                'attributeTax' => $attributeTaxMock,
            ]
        );

        $model->afterDelete(null);
    }

    public function testGetTable()
    {
        $attributeTaxMock = $this->getMockBuilder(\Magento\Weee\Model\ResourceModel\Attribute\Backend\Weee\Tax::class)
            ->setMethods(['getTable'])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeTaxMock
            ->expects($this->once())
            ->method('getTable')
            ->with('weee_tax')
            ->will($this->returnValue(null));

        $model = $this->objectManager->getObject(
            \Magento\Weee\Model\Attribute\Backend\Weee\Tax::class,
            [
                'attributeTax' => $attributeTaxMock,
            ]
        );

        $model->getTable();
    }
}
