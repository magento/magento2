<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures;

use Magento\Setup\Fixtures\ConfigurableProductsFixture;
use Magento\Setup\Model\Complex\Generator;

class ConfigurableProductsFixtureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Fixtures\FixtureModel
     */
    private $fixtureModelMock;

    /**
     * @var \Magento\Setup\Fixtures\ConfigurableProductsFixture
     */
    private $model;

    public function setUp()
    {
        $this->fixtureModelMock = $this->getMock(\Magento\Setup\Fixtures\FixtureModel::class, [], [], '', false);

        $this->model = new ConfigurableProductsFixture($this->fixtureModelMock);
    }

    public function testExecute()
    {
        $importMock = $this->getMock(\Magento\ImportExport\Model\Import::class, [], [], '', false);

        $contextMock = $this->getMock(\Magento\Framework\Model\ResourceModel\Db\Context::class, [], [], '', false);
        $abstractDbMock = $this->getMockForAbstractClass(
            \Magento\Framework\Model\ResourceModel\Db\AbstractDb::class,
            [$contextMock],
            '',
            true,
            true,
            true,
            ['getAllChildren']
        );
        $abstractDbMock->expects($this->once())
            ->method('getAllChildren')
            ->will($this->returnValue([1]));

        $categoryMock = $this->getMock(\Magento\Catalog\Model\Category::class, [], [], '', false);
        $categoryMock->expects($this->once())
            ->method('getResource')
            ->will($this->returnValue($abstractDbMock));
        $categoryMock->expects($this->exactly(3))
            ->method('getName')
            ->will($this->returnValue('category_name'));
        $categoryMock->expects($this->once())
            ->method('getPath')
            ->will($this->returnValue('path/to/category'));
        $categoryMock->expects($this->exactly(4))
            ->method('load')
            ->willReturnSelf();

        $storeMock = $this->getMock(\Magento\Store\Model\Store::class, [], [], '', false);
        $storeMock->expects($this->once())
            ->method('getRootCategoryId')
            ->will($this->returnValue([2]));

        $websiteMock = $this->getMock(\Magento\Store\Model\Website::class, [], [], '', false);
        $websiteMock->expects($this->once())
            ->method('getCode')
            ->will($this->returnValue('website_code'));
        $websiteMock->expects($this->once())
            ->method('getGroups')
            ->will($this->returnValue([$storeMock]));

        $storeManagerMock = $this->getMock(\Magento\Store\Model\StoreManager::class, [], [], '', false);
        $storeManagerMock->expects($this->once())
            ->method('getWebsites')
            ->will($this->returnValue([$websiteMock]));

        $source = $this->getMockBuilder(Generator::class)->disableOriginalConstructor()->getMock();

        $objectManagerMock = $this->getMock(\Magento\Framework\ObjectManager\ObjectManager::class, [], [], '', false);

        $objectManagerMock->expects($this->at(0))
            ->method('get')
            ->with(\Magento\Store\Model\StoreManager::class)
            ->willReturn($storeManagerMock);

        $objectManagerMock->expects($this->at(1))
            ->method('create')
            ->will($this->returnValue($categoryMock));

        $objectManagerMock->expects($this->at(2))
            ->method('create')
            ->with(\Magento\ImportExport\Model\Import::class)
            ->willReturn($importMock);

        $objectManagerMock->expects($this->at(3))
            ->method('create')
            ->with(Generator::class)
            ->willReturn($source);
        $importMock->expects($this->once())->method('validateSource')->with($source)->willReturn(1);
        $importMock->expects($this->once())->method('importSource')->willReturn(1);

        $this->fixtureModelMock
            ->expects($this->any())
            ->method('getValue')
            ->willReturnMap([
                ['configurable_products', 0, 1],
                ['configurable_products_variation', 3, 1],
            ]);

        $this->fixtureModelMock
            ->expects($this->atLeastOnce())
            ->method('getObjectManager')
            ->will($this->returnValue($objectManagerMock));

        $this->model->execute();
    }

    public function testNoFixtureConfigValue()
    {
        $importMock = $this->getMock(\Magento\ImportExport\Model\Import::class, [], [], '', false);
        $importMock->expects($this->never())->method('validateSource');
        $importMock->expects($this->never())->method('importSource');

        $objectManagerMock = $this->getMock(\Magento\Framework\ObjectManager\ObjectManager::class, [], [], '', false);
        $objectManagerMock->expects($this->never())
            ->method('create')
            ->with($this->equalTo(\Magento\ImportExport\Model\Import::class))
            ->willReturn($importMock);

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
        $this->assertSame('Generating configurable products', $this->model->getActionTitle());
    }

    public function testIntroduceParamLabels()
    {
        $this->assertSame([
            'configurable_products' => 'Configurable products',
        ], $this->model->introduceParamLabels());
    }
}
