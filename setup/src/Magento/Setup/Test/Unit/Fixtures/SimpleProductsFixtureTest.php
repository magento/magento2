<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures;

use \Magento\Setup\Fixtures\SimpleProductsFixture;

class SimpleProductsFixtureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Fixtures\FixtureModel
     */
    private $fixtureModelMock;

    public function setUp()
    {
        $this->fixtureModelMock = $this->getMockBuilder('\Magento\Setup\Fixtures\FixtureModel')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testExecute()
    {
        $storeMock = $this->getMockBuilder('\Magento\Store\Model\Store')->disableOriginalConstructor()->getMock();
        $storeMock->expects($this->once())
            ->method('getRootCategoryId')
            ->willReturn(1);

        $websiteMock = $this->getMockBuilder('\Magento\Store\Model\Website')->disableOriginalConstructor()->getMock();
        $websiteMock->expects($this->once())
            ->method('getCode')
            ->willReturn('website_code');
        $websiteMock->expects($this->once())
            ->method('getGroups')
            ->willReturn([$storeMock]);

        $storeManagerMock = $this->getMockBuilder('Magento\Store\Model\StoreManager')
            ->disableOriginalConstructor()
            ->getMock();
        $storeManagerMock->expects($this->once())
            ->method('getWebsites')
            ->willReturn([$websiteMock]);

        $importMock = $this->getMockBuilder('\Magento\ImportExport\Model\Import')
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock = $this->getMockBuilder('\Magento\Framework\Model\Resource\Db\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $abstractDbMock = $this->getMockBuilder('\Magento\Framework\Model\Resource\Db\AbstractDb')
            ->setConstructorArgs([$contextMock])
            ->setMethods(['getAllChildren'])
            ->getMockForAbstractClass();
        $abstractDbMock->expects($this->any())
            ->method('getAllChildren')
            ->will($this->returnValue([1]));

        $categoryMock = $this->getMockBuilder('Magento\Catalog\Model\Category')
            ->disableOriginalConstructor()
            ->getMock();
        $categoryMock->expects($this->once())
            ->method('getResource')
            ->willReturn($abstractDbMock);
        $categoryMock->expects($this->once())
            ->method('getPath')
            ->willReturn('path/to/category');
        $categoryMock->expects($this->any())
            ->method('load')
            ->willReturnSelf();
        $categoryMock->expects($this->any())
            ->method('getName')
            ->willReturn('category_name');

        $objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManager\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerMock->expects($this->exactly(2))
            ->method('create')
            ->will($this->onConsecutiveCalls($storeManagerMock, $importMock));
        $objectManagerMock->expects($this->once())
            ->method('get')
            ->willReturn($categoryMock);

        $this->fixtureModelMock
            ->expects($this->once())
            ->method('getValue')
            ->willReturn(1);
        $this->fixtureModelMock
            ->expects($this->exactly(3))
            ->method('getObjectManager')
            ->willReturn($objectManagerMock);

        $simpleProductsFixture = new SimpleProductsFixture($this->fixtureModelMock);
        $simpleProductsFixture->execute();
    }

    public function testGetActionTitle()
    {
        $simpleProductsFixture = new SimpleProductsFixture($this->fixtureModelMock);
        $this->assertSame('Generating simple products', $simpleProductsFixture->getActionTitle());
    }

    public function testIntroduceParamLabels()
    {
        $simpleProductsFixture = new SimpleProductsFixture($this->fixtureModelMock);
        $this->assertSame([
            'simple_products' => 'Simple products'
        ], $simpleProductsFixture->introduceParamLabels());
    }
}
