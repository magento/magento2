<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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

    /**
     * @var \Magento\Setup\Fixtures\SimpleProductsFixture
     */
    private $model;

    public function setUp()
    {
        $this->fixtureModelMock = $this->getMockBuilder(\Magento\Setup\Fixtures\FixtureModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new SimpleProductsFixture($this->fixtureModelMock);
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    public function testExecute()
    {
        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock->expects($this->once())
            ->method('getRootCategoryId')
            ->willReturn(1);

        $websiteMock = $this->getMockBuilder(\Magento\Store\Model\Website::class)
            ->disableOriginalConstructor()
            ->getMock();
        $websiteMock->expects($this->once())
            ->method('getCode')
            ->willReturn('website_code');
        $websiteMock->expects($this->once())
            ->method('getGroups')
            ->willReturn([$storeMock]);

        $storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeManagerMock->expects($this->once())
            ->method('getWebsites')
            ->willReturn([$websiteMock]);

        $source = $this->getMockBuilder(\Magento\Setup\Model\Generator::class)->disableOriginalConstructor()->getMock();

        $importMock = $this->getMockBuilder(\Magento\ImportExport\Model\Import::class)
            ->disableOriginalConstructor()
            ->getMock();
        $importMock->expects($this->once())->method('validateSource')->with($source)->willReturn(1);
        $importMock->expects($this->once())->method('importSource')->willReturn(1);

        $contextMock = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
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

        $categoryMock = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->disableOriginalConstructor()
            ->getMock();
        $categoryMock->expects($this->once())
            ->method('getResource')
            ->willReturn($abstractDbMock);
        $categoryMock->expects($this->once())
            ->method('getPath')
            ->willReturn('path/to/category');
        $categoryMock->expects($this->exactly(5))
            ->method('load')
            ->willReturnSelf();
        $categoryMock->expects($this->exactly(3))
            ->method('getName')
            ->willReturn('category_name');

        $objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManager\ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerMock->expects($this->at(0))
            ->method('create')
            ->with(\Magento\Store\Model\StoreManager::class)
            ->willReturn($storeManagerMock);
        $objectManagerMock->expects($this->at(1))
            ->method('get')
            ->willReturn($categoryMock);
        $objectManagerMock->expects($this->at(2))
            ->method('create')
            ->with(\Magento\Setup\Model\Generator::class)
            ->willReturn($source);
        $objectManagerMock->expects($this->at(3))
            ->method('create')
            ->with(\Magento\ImportExport\Model\Import::class)
            ->willReturn($importMock);

        $valuesMap = [
            ['simple_products', 0, 1],
            ['configurable_products', 0, 1],
            ['search_terms', null, ['search_term' =>[['term' => 'iphone 6', 'count' => '1']]]],
            [
                'search_config',
                null,
                [
                    'max_amount_of_words_description' => '200',
                    'max_amount_of_words_short_description' => '20',
                    'min_amount_of_words_description' => '20',
                    'min_amount_of_words_short_description' => '5'
                ]
            ],
            [
                'attribute_sets',
                null,
                [
                    'attribute_set' => [
                        [
                            'name' => 'attribute set name',
                            'attributes' => [
                                'attribute' => [
                                    [
                                        'is_required' => 1,
                                        'is_visible_on_front' => 1,
                                        'is_visible_in_advanced_search' => 1,
                                        'is_filterable' => 1,
                                        'is_filterable_in_search' => 1,
                                        'default_value' => 'yellow1',
                                        'attribute_code' => 'mycolor',
                                        'is_searchable' => '1',
                                        'frontend_label' => 'mycolor',
                                        'frontend_input' => 'select',
                                        'options' => [
                                            'option' => [
                                                [
                                                    'label' => 'yellow1',
                                                    'value' => ''
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $this->fixtureModelMock
            ->expects($this->any())
            ->method('getValue')
            ->will($this->returnValueMap($valuesMap));
        $this->fixtureModelMock
            ->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($objectManagerMock);

        $this->model->execute();
    }

    public function testNoFixtureConfigValue()
    {
        $importMock = $this->getMockBuilder(\Magento\ImportExport\Model\Import::class)
            ->disableOriginalConstructor()
            ->getMock();
        $importMock->expects($this->never())->method('validateSource');
        $importMock->expects($this->never())->method('importSource');

        $objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManager\ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerMock->expects($this->never())
            ->method('create')
            ->with($this->equalTo(\Magento\ImportExport\Model\Import::class))
            ->willReturn($importMock);

        $this->fixtureModelMock
            ->expects($this->never())
            ->method('getObjectManager')
            ->will($this->returnValue($objectManagerMock));
        $this->fixtureModelMock
            ->expects($this->any())
            ->method('getValue')
            ->willReturn(false);

        $this->model->execute();
    }

    public function testGetActionTitle()
    {
        $this->assertSame('Generating simple products', $this->model->getActionTitle());
    }

    public function testIntroduceParamLabels()
    {
        $this->assertSame([
            'simple_products' => 'Simple products'
        ], $this->model->introduceParamLabels());
    }
}
