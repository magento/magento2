<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures;

use \Magento\Setup\Fixtures\CatalogPriceRulesFixture;

class CatalogPriceRulesFixtureTest extends \PHPUnit_Framework_TestCase
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
        $storeMock = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);
        $storeMock
            ->expects($this->once())
            ->method('getRootCategoryId')
            ->will($this->returnValue(2));

        $websiteMock = $this->getMock('\Magento\Store\Model\Website', [], [], '', false);
        $websiteMock
            ->expects($this->once())
            ->method('getGroups')
            ->will($this->returnValue([$storeMock]));
        $websiteMock
            ->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('website_id'));

        $storeManagerMock = $this->getMock('Magento\Store\Model\StoreManager', [], [], '', false);
        $storeManagerMock
            ->expects($this->once())
            ->method('getWebsites')
            ->will($this->returnValue([$websiteMock]));

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

        $categoryMock = $this->getMock('Magento\Catalog\Model\Category', [], [], '', false);
        $categoryMock
            ->expects($this->any())
            ->method('getResource')
            ->will($this->returnValue($abstractDbMock));
        $categoryMock
            ->expects($this->once())
            ->method('getPath')
            ->will($this->returnValue('path/to/file'));
        $categoryMock
            ->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('category_id'));

        $modelMock = $this->getMock('\Magento\SalesRule\Model\Rule', [], [], '', false);
        $modelMock
            ->expects($this->once())
            ->method('getIdFieldName')
            ->will($this->returnValue('Field Id Name'));


        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManager\ObjectManager', [], [], '', false);
        $objectManagerMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($storeManagerMock));
        $objectManagerMock
            ->expects($this->exactly(2))
            ->method('get')
            ->will($this->onConsecutiveCalls($categoryMock, $modelMock));

        $this->fixtureModelMock
            ->expects($this->once())
            ->method('resetObjectManager');
        $this->fixtureModelMock
            ->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue(1));
        $this->fixtureModelMock
            ->expects($this->exactly(3))
            ->method('getObjectManager')
            ->will($this->returnValue($objectManagerMock));


        $catalogPriceFixture = new CatalogPriceRulesFixture($this->fixtureModelMock);
        $catalogPriceFixture->execute();
    }

    public function testGetActionTitle()
    {
        $catalogPriceFixture = new CatalogPriceRulesFixture($this->fixtureModelMock);
        $this->assertSame('Generating catalog price rules', $catalogPriceFixture->getActionTitle());
    }

    public function testIntroduceParamLabels()
    {
        $catalogPriceFixture = new CatalogPriceRulesFixture($this->fixtureModelMock);
        $this->assertSame([
            'catalog_price_rules' => 'Catalog Price Rules'
        ], $catalogPriceFixture->introduceParamLabels());
    }
}
