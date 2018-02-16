<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Map;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\CatalogUrlRewrite\Model\Map\HashMapPool;
use Magento\CatalogUrlRewrite\Model\Map\DataCategoryHashMap;
use Magento\CatalogUrlRewrite\Model\Map\DataProductHashMap;
use Magento\CatalogUrlRewrite\Model\Map\DataCategoryUsedInProductsHashMap;
use Magento\Framework\ObjectManagerInterface;

/**
 * Tests HashMapPool class.
 */
class HashMapPoolTest extends \PHPUnit_Framework_TestCase
{
    /** @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $objectManagerMock;

    /** @var HashMapPool|\PHPUnit_Framework_MockObject_MockObject */
    private $model;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMock(ObjectManagerInterface::class);

        $this->model = (new ObjectManager($this))->getObject(
            HashMapPool::class,
            [
                'objectManager' => $this->objectManagerMock,
            ]
        );
    }

    /**
     * Tests getDataMap().
     */
    public function testGetDataMap()
    {
        $dataCategoryMapMock = $this->getMock(DataCategoryHashMap::class, [], [], '', false);
        $dataProductMapMock = $this->getMock(DataProductHashMap::class, [], [], '', false);
        $dataProductMapMockOtherCategory = $this->getMock(DataCategoryUsedInProductsHashMap::class, [], [], '', false);

        $this->objectManagerMock->expects($this->any())
            ->method('create')
            ->willReturnMap(
                [
                    [
                        DataCategoryHashMap::class,
                        ['category' => 1],
                        $dataCategoryMapMock
                    ],
                    [
                        DataProductHashMap::class,
                        ['category' => 1],
                        $dataProductMapMock
                    ],
                    [
                        DataCategoryUsedInProductsHashMap::class,
                        ['category' => 2],
                        $dataProductMapMockOtherCategory
                    ]
                ]
            );
        $this->assertSame($dataCategoryMapMock, $this->model->getDataMap(DataCategoryHashMap::class, 1));
        $this->assertSame($dataProductMapMock, $this->model->getDataMap(DataProductHashMap::class, 1));
        $this->assertSame(
            $dataProductMapMockOtherCategory,
            $this->model->getDataMap(DataCategoryUsedInProductsHashMap::class, 2)
        );
    }

    /**
     * Tests getDataMap() with exception.
     */
    public function testGetDataMapException()
    {
        $nonInterface = $this->getMock(HashMapPool::class, [], [], '', false);

        $this->objectManagerMock->expects($this->any())
            ->method('create')
            ->willReturn($nonInterface);
        $this->setExpectedException(\InvalidArgumentException::class);
        $this->model->getDataMap(HashMapPool::class, 1);
    }
}
