<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures;

use \Magento\Setup\Fixtures\CustomersFixture;

class CustomersFixtureTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Fixtures\FixtureModel
     */
    private $fixtureModelMock;

    public function setUp()
    {
        $this->fixtureModelMock = $this->getMockBuilder('\Magento\Setup\Fixtures\FixtureModel')->disableOriginalConstructor()->getMock();
    }

    public function testExecute()
    {
        $importMock = $this->getMockBuilder('\Magento\ImportExport\Model\Import')->disableOriginalConstructor()->getMock();

        $storeMock = $this->getMockBuilder('\Magento\Store\Model\Store')->disableOriginalConstructor()->getMock();
        $storeMock->expects($this->once())
            ->method('getCode')
            ->will($this->returnValue('store_code'));

        $websiteMock = $this->getMockBuilder('\Magento\Store\Model\Website')->disableOriginalConstructor()->getMock();
        $websiteMock->expects($this->once())
            ->method('getCode')
            ->will($this->returnValue('website_code'));

        $storeManagerMock = $this->getMockBuilder('Magento\Store\Model\StoreManager')->disableOriginalConstructor()->getMock();
        $storeManagerMock->expects($this->once())
            ->method('getDefaultStoreView')
            ->will($this->returnValue($storeMock));
        $storeManagerMock->expects($this->once())
            ->method('getWebsites')
            ->will($this->returnValue([$websiteMock]));

        $objectManagerMode = $this->getMockBuilder('Magento\Framework\ObjectManager\ObjectManager')->disableOriginalConstructor()->getMock();
        $objectManagerMode->expects($this->exactly(2))
            ->method('create')
            ->will($this->onConsecutiveCalls($storeManagerMock, $importMock));

        $this->fixtureModelMock
            ->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue(1));
        $this->fixtureModelMock
            ->expects($this->exactly(2))
            ->method('getObjectManager')
            ->will($this->returnValue($objectManagerMode));

        $customersFixture = new CustomersFixture($this->fixtureModelMock);
        $customersFixture->execute();
    }

    public function testGetActionTitle()
    {
        $customersFixture = new CustomersFixture($this->fixtureModelMock);
        $this->assertSame('Generating customers', $customersFixture->getActionTitle());
    }

    public function testIntroduceParamLabels()
    {
        $customersFixture = new CustomersFixture($this->fixtureModelMock);
        $this->assertSame([
            'customers' => 'Customers'
        ], $customersFixture->introduceParamLabels());
    }
}
