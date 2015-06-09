<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures;

use Magento\Catalog\Model\Indexer\Product\Eav;
use \Magento\Setup\Fixtures\EavVariationsFixture;

class EavVariationsFixtureTest extends \PHPUnit_Framework_TestCase
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
        $attributeMock = $this->getMockBuilder('Magenot\Catalog\Model\Resource\Eav\Attribute')
            ->setMethods(['setAttributeGroupId', 'addData', 'setAttributeSetId', 'save'])
            ->getMock();
        $attributeMock->expects($this->exactly(2))
            ->method('setAttributeSetId')
            ->willReturnSelf();
        $attributeMock->expects($this->once())
            ->method('setAttributeGroupId')
            ->willReturnSelf();

        $storeMock = $this->getMockBuilder('\Magento\Store\Model\Store')->disableOriginalConstructor()->getMock();

        $storeManagerMock = $this->getMockBuilder('Magento\Store\Model\StoreManager')
            ->disableOriginalConstructor()
            ->getMock();
        $storeManagerMock->expects($this->once())
            ->method('getStores')
            ->will($this->returnValue([$storeMock]));

        $setMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\Set')
            ->disableOriginalConstructor()
            ->getMock();
        $setMock->expects($this->once())
            ->method('getDefaultGroupId')
            ->will($this->returnValue(2));

        $cacheMock = $this->getMockBuilder('Magento\Framework\App\CacheInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManager\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerMock->expects($this->exactly(2))
            ->method('create')
            ->will($this->onConsecutiveCalls($attributeMock, $storeManagerMock));
        $objectManagerMock->expects($this->exactly(2))
            ->method('get')
            ->will($this->onConsecutiveCalls($setMock, $cacheMock));

        $this->fixtureModelMock
            ->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue(1));
        $this->fixtureModelMock
            ->expects($this->exactly(4))
            ->method('getObjectManager')
            ->will($this->returnValue($objectManagerMock));

        $eavVariationsFixture = new EavVariationsFixture($this->fixtureModelMock);
        $eavVariationsFixture->execute();
    }

    public function testGetActionTitle()
    {
        $eavVariationsFixture = new EavVariationsFixture($this->fixtureModelMock);
        $this->assertSame('Generating configurable EAV variations', $eavVariationsFixture->getActionTitle());
    }

    public function testIntroduceParamLabels()
    {
        $eavVariationsFixture = new EavVariationsFixture($this->fixtureModelMock);
        $this->assertSame([], $eavVariationsFixture->introduceParamLabels());
    }
}
