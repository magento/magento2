<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Map;

use Magento\CatalogUrlRewrite\Model\Map\DatabaseMapPool;
use Magento\CatalogUrlRewrite\Model\Map\DataCategoryUrlRewriteDatabaseMap;
use Magento\CatalogUrlRewrite\Model\Map\DataProductUrlRewriteDatabaseMap;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\ObjectManagerInterface;

/**
 * Tests DatabaseMapPool class.
 */
class DatabaseMapPoolTest extends \PHPUnit_Framework_TestCase
{
    /** @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $objectManagerMock;

    /** @var DatabaseMapPool|\PHPUnit_Framework_MockObject_MockObject */
    private $model;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMock(ObjectManagerInterface::class);

        $this->model = (new ObjectManager($this))->getObject(
            DatabaseMapPool::class,
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
        $dataCategoryMapMock = $this->getMock(DataCategoryUrlRewriteDatabaseMap::class, [], [], '', false);
        $dataProductMapMock = $this->getMock(DataProductUrlRewriteDatabaseMap::class, [], [], '', false);

        $this->objectManagerMock->expects($this->any())
            ->method('create')
            ->willReturnMap(
                [
                    [
                        DataCategoryUrlRewriteDatabaseMap::class,
                        ['category' => 1],
                        $dataCategoryMapMock
                    ],
                    [
                        DataProductUrlRewriteDatabaseMap::class,
                        ['category' => 1],
                        $dataProductMapMock
                    ]
                ]
            );
        $this->assertSame($dataCategoryMapMock, $this->model->getDataMap(DataCategoryUrlRewriteDatabaseMap::class, 1));
        $this->assertSame($dataProductMapMock, $this->model->getDataMap(DataProductUrlRewriteDatabaseMap::class, 1));
    }

    /**
     * Tests getDataMap() with exception.
     */
    public function testGetDataMapException()
    {
        $nonInterface = $this->getMock(DatabaseMapPool::class, [], [], '', false);

        $this->objectManagerMock->expects($this->any())
            ->method('create')
            ->willReturn($nonInterface);
        $this->setExpectedException(\InvalidArgumentException::class);
        $this->model->getDataMap(DatabaseMapPool::class, 1);
    }
}
