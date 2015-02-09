<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Resource;

use PHPUnit_Framework_TestCase;

class AdvancedTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogSearch\Model\Resource\Advanced
     */
    private $model;

    /**
     * setUp method for AdvancedTest
     */
    protected function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $storeManager = $this->getStoreManager();

        $this->model = $helper->getObject(
            'Magento\CatalogSearch\Model\Resource\Advanced',
            [
                'storeManager' => $storeManager
            ]
        );
    }

    /**
     * @dataProvider indexableAttributeDataProvider
     */
    public function testAddIndexableAttributeModifiedFilter($indexType, $value, $expected)
    {
        $selectMock = $this->getMock('Magento\Framework\DB\Select', [], [], '', false);
        $collectionMock = $this->getMock('Magento\CatalogSearch\Model\Resource\Advanced\Collection', [], [], '', false);
        $collectionMock->expects($this->once())->method('getSelect')->willReturn($selectMock);

        $attributeMock = $this->getMock('Magento\Catalog\Model\Resource\Eav\Attribute', [], [], '', false);
        $attributeMock->expects($this->once())->method('getIndexType')->willReturn($indexType);
        $attributeMock->expects($this->any())->method('getAttributeId')->willReturn(1);

        $this->assertEquals(
            $expected,
            $this->model->addIndexableAttributeModifiedFilter($collectionMock, $attributeMock, $value)
        );
    }

    public function indexableAttributeDataProvider()
    {
        return [
            ['decimal', '', true],
            ['source', ['from' => 0, 'to' => 0], false],
            [false, ['from' => 0], true],
            ['decimal', ['to' => 0], true],
            ['source', ['from' => 1, 'to' => 1], true]
        ];
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getStoreManager()
    {
        $store = $this->getMockBuilder('Magento\Store\Model\Store')
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $storeManager = $this->getMockBuilder('Magento\Framework\Store\StoreManagerInterface')
            ->setMethods(['getStore'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($store);

        return $storeManager;
    }
}
