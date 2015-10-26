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

    /**
     * @var \Magento\Setup\Fixtures\ConfigurableProductsFixture
     */
    private $model;

    public function setUp()
    {
        $this->fixtureModelMock = $this->getMock('\Magento\Setup\Fixtures\FixtureModel', [], [], '', false);

        $this->model = new ConfigurableProductsFixture($this->fixtureModelMock);
    }

    public function testExecute()
    {
        $importMock = $this->getMock('\Magento\ImportExport\Model\Import', [], [], '', false);

        $contextMock = $this->getMock('\Magento\Framework\Model\ResourceModel\Db\Context', [], [], '', false);
        $abstractDbMock = $this->getMockForAbstractClass(
            '\Magento\Framework\Model\ResourceModel\Db\AbstractDb',
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

        $categoryMock = $this->getMock('Magento\Catalog\Model\Category', [], [], '', false);
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

        $storeMock = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);
        $storeMock->expects($this->once())
            ->method('getRootCategoryId')
            ->will($this->returnValue([2]));

        $websiteMock = $this->getMock('\Magento\Store\Model\Website', [], [], '', false);
        $websiteMock->expects($this->once())
            ->method('getCode')
            ->will($this->returnValue('website_code'));
        $websiteMock->expects($this->once())
            ->method('getGroups')
            ->will($this->returnValue([$storeMock]));

        $storeManagerMock = $this->getMock('Magento\Store\Model\StoreManager', [], [], '', false);
        $storeManagerMock->expects($this->once())
            ->method('getWebsites')
            ->will($this->returnValue([$websiteMock]));

        $valueMap = [
            [
                'Magento\ImportExport\Model\Import',
                [
                    'data' => [
                        'entity' => 'catalog_product',
                        'behavior' => 'append',
                        'validation_strategy' => 'validation-stop-on-errors'
                    ]
                ],
                $importMock
            ],
            ['Magento\Store\Model\StoreManager', [], $storeManagerMock]
        ];

        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManager\ObjectManager', [], [], '', false);
        $objectManagerMock->expects($this->exactly(2))
            ->method('create')
            ->will($this->returnValueMap($valueMap));
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

        $this->model->execute();
    }

    public function testNoFixtureConfigValue()
    {
        $importMock = $this->getMock('\Magento\ImportExport\Model\Import', [], [], '', false);
        $importMock->expects($this->never())->method('validateSource');
        $importMock->expects($this->never())->method('importSource');

        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManager\ObjectManager', [], [], '', false);
        $objectManagerMock->expects($this->never())
            ->method('create')
            ->with($this->equalTo('Magento\ImportExport\Model\Import'))
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
            'configurable_products' => 'Configurable products'
        ], $this->model->introduceParamLabels());
    }
}
