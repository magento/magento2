<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures;

use Magento\Catalog\Model\Indexer\Product\Eav;
use Magento\Setup\Fixtures\EavVariationsFixture;

class EavVariationsFixtureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Fixtures\FixtureModel
     */
    private $fixtureModelMock;

    /**
     * @var \Magento\Setup\Fixtures\EavVariationsFixture
     */
    private $model;

    public function setUp()
    {
        $this->fixtureModelMock = $this->getMock(\Magento\Setup\Fixtures\FixtureModel::class, [], [], '', false);

        $this->model = new EavVariationsFixture($this->fixtureModelMock);
    }

    public function testExecute()
    {
        $attributeMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class,
            [
                'setAttributeSetId',
                'setAttributeGroupId',
                'save',
            ],
            [],
            '',
            false
        );
        $attributeMock->expects($this->exactly(2))
            ->method('setAttributeSetId')
            ->willReturnSelf();
        $attributeMock->expects($this->once())
            ->method('setAttributeGroupId')
            ->willReturnSelf();

        $storeMock = $this->getMock(\Magento\Store\Model\Store::class, [], [], '', false);

        $storeManagerMock = $this->getMock(\Magento\Store\Model\StoreManager::class, [], [], '', false);
        $storeManagerMock->expects($this->once())
            ->method('getStores')
            ->will($this->returnValue([$storeMock]));

        $setMock = $this->getMock(\Magento\Eav\Model\Entity\Attribute\Set::class, [], [], '', false);
        $setMock->expects($this->once())
            ->method('getDefaultGroupId')
            ->will($this->returnValue(2));

        $cacheMock = $this->getMock(\Magento\Framework\App\CacheInterface::class, [], [], '', false);

        $valueMap = [
            [\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class, [], $attributeMock],
            [\Magento\Store\Model\StoreManager::class, [], $storeManagerMock],
            [\Magento\Eav\Model\Entity\Attribute\Set::class, $setMock],
            [\Magento\Framework\App\CacheInterface::class, $cacheMock],
        ];

        $objectManagerMock = $this->getMock(\Magento\Framework\ObjectManager\ObjectManager::class, [], [], '', false);
        $objectManagerMock->expects($this->exactly(2))
            ->method('create')
            ->will($this->returnValueMap($valueMap));
        $objectManagerMock->expects($this->exactly(2))
            ->method('get')
            ->will($this->returnValueMap($valueMap));

        $this->fixtureModelMock
            ->expects($this->any())
            ->method('getValue')
            ->willReturnMap([
                ['configurable_products', 0, 1],
                ['configurable_products_variation', 3, 1],
            ]);

        $this->fixtureModelMock
            ->expects($this->exactly(4))
            ->method('getObjectManager')
            ->will($this->returnValue($objectManagerMock));

        $this->model->execute();
    }

    public function testNoFixtureConfigValue()
    {
        $attributeMock = $this->getMock(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class, [], [], '', false);
        $attributeMock->expects($this->never())->method('save');

        $objectManagerMock = $this->getMock(\Magento\Framework\ObjectManager\ObjectManager::class, [], [], '', false);
        $objectManagerMock->expects($this->never())
            ->method('create')
            ->with($this->equalTo(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class))
            ->willReturn($attributeMock);

        $this->fixtureModelMock
            ->expects($this->never())
            ->method('getObjectManager')
            ->will($this->returnValue($objectManagerMock));
        $this->fixtureModelMock
            ->expects($this->once())
            ->method('getValue')
            ->willReturn(false);

        $this->model->execute();
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
