<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * @var \Magento\Weee\Model\Attribute\Backend\Weee\Tax
     */
    protected $model;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->model = $this->objectManager->getObject('Magento\Weee\Model\Attribute\Backend\Weee\Tax');
    }

    public function testGetBackendModelName()
    {
        $this->assertEquals('Magento\Weee\Model\Attribute\Backend\Weee\Tax', $this->model->getBackendModelName());
    }

    public function testValidate()
    {
        $attributeMock = $this->getMockBuilder('Magento\Eav\Model\Attribute')
            ->setMethods(['getName'])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeMock
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('weeeTax'));

        $modelMock = $this->getMockBuilder('Magento\Weee\Model\Attribute\Backend\Weee\Tax')
            ->setMethods(['getAttribute'])
            ->disableOriginalConstructor()
            ->getMock();
        $modelMock
            ->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($attributeMock));

        $taxes = [['state' => 12, 'country' => 'US', 'website_id' => '1']];
        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $productMock
            ->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($taxes));

        // No exception
        $modelMock->validate($productMock);

        $taxes = [['state' => 12, 'country' => 'US', 'website_id' => '1'],
            ['state' => 12, 'country' => 'US', 'website_id' => '1']];
        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $productMock
            ->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($taxes));

        // Exception caught
        $this->setExpectedException('Exception',
            'We found a duplicate of website, country and state fields for a fixed product tax');
        $modelMock->validate($productMock);
    }

    public function testAfterLoad()
    {
        $data = [['website_id' => 1, 'value' => 1]];

        $attributeTaxMock = $this->getMockBuilder('Magento\Weee\Model\ResourceModel\Attribute\Backend\Weee\Tax')
            ->setMethods(['loadProductData'])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeTaxMock
            ->expects($this->any())
            ->method('loadProductData')
            ->will($this->returnValue($data));

        $attributeMock = $this->getMockBuilder('Magento\Eav\Model\Attribute')
            ->setMethods(['getName'])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeMock
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('weeeTax'));

        $model = $this->objectManager->getObject('Magento\Weee\Model\Attribute\Backend\Weee\Tax',
            [
                'attributeTax' => $attributeTaxMock,
                '_attribute' => $attributeMock
            ]
        );

        $model->setAttribute($attributeMock);
        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
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
        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
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

        $attributeTaxMock = $this->getMockBuilder('Magento\Weee\Model\ResourceModel\Attribute\Backend\Weee\Tax')
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

        $attributeMock = $this->getMockBuilder('Magento\Eav\Model\Attribute')
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

        $model = $this->objectManager->getObject('Magento\Weee\Model\Attribute\Backend\Weee\Tax',
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
        $attributeTaxMock = $this->getMockBuilder('Magento\Weee\Model\ResourceModel\Attribute\Backend\Weee\Tax')
            ->setMethods(['deleteProductData'])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeTaxMock
            ->expects($this->once())
            ->method('deleteProductData')
            ->with(null, null)
            ->will($this->returnValue(null));

        $model = $this->objectManager->getObject('Magento\Weee\Model\Attribute\Backend\Weee\Tax',
            [
                'attributeTax' => $attributeTaxMock,
            ]
        );

        $model->afterDelete(null);
    }

    public function testGetTable()
    {
        $attributeTaxMock = $this->getMockBuilder('Magento\Weee\Model\ResourceModel\Attribute\Backend\Weee\Tax')
            ->setMethods(['getTable'])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeTaxMock
            ->expects($this->once())
            ->method('getTable')
            ->with('weee_tax')
            ->will($this->returnValue(null));

        $model = $this->objectManager->getObject('Magento\Weee\Model\Attribute\Backend\Weee\Tax',
            [
                'attributeTax' => $attributeTaxMock,
            ]
        );

        $model->getTable();
    }
}
