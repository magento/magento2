<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures;

use \Magento\Setup\Fixtures\CartPriceRulesFixture;

class CartPriceRulesFixtureTest extends \PHPUnit_Framework_TestCase
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
        $storeMock = $this->getMockBuilder('\Magento\Store\Model\Store')->disableOriginalConstructor()->getMock();
        $storeMock->expects($this->once())
            ->method('getRootCategoryId')
            ->will($this->returnValue(2));

        $websiteMock = $this->getMockBuilder('\Magento\Store\Model\Website')->disableOriginalConstructor()->getMock();
        $websiteMock->expects($this->once())
            ->method('getGroups')
            ->will($this->returnValue([$storeMock]));
        $websiteMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('website_id'));

        $storeManagerMock = $this->getMockBuilder('Magento\Store\Model\StoreManager')->disableOriginalConstructor()->getMock();
        $storeManagerMock->expects($this->once())
            ->method('getWebsites')
            ->will($this->returnValue([$websiteMock]));

        $contextMock = $this->getMockBuilder('\Magento\Framework\Model\Resource\Db\Context')->disableOriginalConstructor()->getMock();
        $abstractDbMock = $this->getMockBuilder('\Magento\Framework\Model\Resource\Db\AbstractDb')->setConstructorArgs([$contextMock])->setMethods(['getAllChildren'])->getMockForAbstractClass();
        $abstractDbMock->expects($this->any())
            ->method('getAllChildren')
            ->will($this->returnValue([1]));

        $categoryMock = $this->getMockBuilder('Magento\Catalog\Model\Category')->disableOriginalConstructor()->getMock();
        $categoryMock->expects($this->any())
            ->method('getResource')
            ->will($this->returnValue($abstractDbMock));
        $categoryMock->expects($this->once())
            ->method('getPath')
            ->will($this->returnValue('path/to/file'));
        $categoryMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('category_id'));

        $modelMock = $this->getMockBuilder('\Magento\SalesRule\Model\Rule')->disableOriginalConstructor()->getMock();
        $modelMock->expects($this->once())
            ->method('getIdFieldName')
            ->will($this->returnValue('Field Id Name'));


        $objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManager\ObjectManager')->disableOriginalConstructor()->getMock();
        $objectManagerMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($storeManagerMock));
        $objectManagerMock->expects($this->exactly(2))
            ->method('get')
            ->will($this->onConsecutiveCalls($categoryMock, $modelMock));

        $this->fixtureModelMock
            ->expects($this->exactly(2))
            ->method('getValue')
            ->will($this->onConsecutiveCalls(1, 3));
        $this->fixtureModelMock
            ->expects($this->exactly(3))
            ->method('getObjectManager')
            ->will($this->returnValue($objectManagerMock));


        $cartPriceFixture = new CartPriceRulesFixture($this->fixtureModelMock);
        $cartPriceFixture->execute();
    }

    public function testGetActionTitle()
    {
        $cartPriceFixture = new CartPriceRulesFixture($this->fixtureModelMock);
        $this->assertSame('Generating shopping cart price rules', $cartPriceFixture->getActionTitle());
    }

    public function testIntroduceParamLabels()
    {
        $cartPriceFixture = new CartPriceRulesFixture($this->fixtureModelMock);
        $this->assertSame([
            'cart_price_rules' => 'Cart Price Rules'
        ], $cartPriceFixture->introduceParamLabels());
    }
}
