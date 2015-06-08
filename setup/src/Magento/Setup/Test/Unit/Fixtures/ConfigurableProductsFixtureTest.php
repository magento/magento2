<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures;

use \Magento\Setup\Fixtures\ConfigurableProductsFixture;

class ConfigurableProductsFixtureTest extends \PHPUnit_Framework_TestCase
{
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

        $contextMock = $this->getMockBuilder('\Magento\Framework\Model\Resource\Db\Context')->disableOriginalConstructor()->getMock();
        $abstractDbMock = $this->getMockBuilder('\Magento\Framework\Model\Resource\Db\AbstractDb')->setConstructorArgs([$contextMock])->setMethods(['getAllChildren'])->getMockForAbstractClass();
        $abstractDbMock->expects($this->any())
            ->method('getAllChildren')
            ->will($this->returnValue([1]));

        $categoryMock = $this->getMockBuilder('Magento\Catalog\Model\Category')->disableOriginalConstructor()->getMock();
        $categoryMock->expects($this->once())
            ->method('getResource')
            ->will($this->returnValue($abstractDbMock));
        $categoryMock->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('category_name'));
        $categoryMock->expects($this->once())
            ->method('getPath')
            ->will($this->returnValue('path/to/category'));
        $categoryMock->expects($this->any())->method('load')
            ->willReturnSelf();

        $storeMock = $this->getMockBuilder('\Magento\Store\Model\Store')->disableOriginalConstructor()->getMock();
        $storeMock->expects($this->once())
            ->method('getRootCategoryId')
            ->will($this->returnValue([2]));

        $websiteMock = $this->getMockBuilder('\Magento\Store\Model\Website')->disableOriginalConstructor()->getMock();
        $websiteMock->expects($this->once())
            ->method('getCode')
            ->will($this->returnValue('website_code'));
        $websiteMock->expects($this->once())
            ->method('getGroups')
            ->will($this->returnValue([$storeMock]));

        $storeManagerMock = $this->getMockBuilder('Magento\Store\Model\StoreManager')->disableOriginalConstructor()->getMock();
        $storeManagerMock->expects($this->once())
            ->method('getWebsites')
            ->will($this->returnValue([$websiteMock]));

        $objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManager\ObjectManager')->disableOriginalConstructor()->getMock();
        $objectManagerMock->expects($this->exactly(2))
            ->method('create')
            ->will($this->onConsecutiveCalls($storeManagerMock, $importMock));
        $objectManagerMock->expects($this->once())
            ->method('get')
            ->will($this->returnValue($categoryMock));

        $this->fixtureModelMock
            ->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue(1));
        $this->fixtureModelMock
            ->expects($this->exactly(3))
            ->method('getObjectManager')
            ->will($this->returnValue($objectManagerMock));

        $configurableProductsFixture = new ConfigurableProductsFixture($this->fixtureModelMock);
        $configurableProductsFixture->execute();
    }

    public function testGetActionTitle()
    {
        $configurableProductsFixture = new ConfigurableProductsFixture($this->fixtureModelMock);
        $this->assertSame('Generating configurable products', $configurableProductsFixture->getActionTitle());
    }

    public function testIntroduceParamLabels()
    {
        $configurableProductsFixture = new ConfigurableProductsFixture($this->fixtureModelMock);
        $this->assertSame([
            'configurable_products' => 'Configurable products'
        ], $configurableProductsFixture->introduceParamLabels());
    }
}
