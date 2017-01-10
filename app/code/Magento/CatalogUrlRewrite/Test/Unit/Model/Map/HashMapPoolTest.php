<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Map;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\CatalogUrlRewrite\Model\Map\HashMapPool;
use Magento\CatalogUrlRewrite\Model\Map\DataCategoryHashMap;
use Magento\CatalogUrlRewrite\Model\Map\DataProductHashMap;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class HashMapPoolTest
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
     * Tests getDataMap
     */
    public function testGetDataMap()
    {
        $dataCategoryMapMock = $this->getMock(DataCategoryHashMap::class, [], [], '', false);
        $dataProductMapMock = $this->getMock(DataProductHashMap::class, [], [], '', false);
        $dataProductMapMockOtherCategory = $this->getMock(DataProductHashMap::class, [], [], '', false);

        $this->objectManagerMock->expects($this->any())
            ->method('create')
            ->willReturnOnConsecutiveCalls($dataCategoryMapMock, $dataProductMapMock, $dataProductMapMockOtherCategory);
        $this->assertEquals($dataCategoryMapMock, $this->model->getDataMap(DataCategoryHashMap::class, 1));
        $this->assertSame($dataCategoryMapMock, $this->model->getDataMap(DataCategoryHashMap::class, 1));
        $this->assertEquals($dataProductMapMock, $this->model->getDataMap(DataProductHashMap::class, 1));
        $this->assertEquals($dataProductMapMockOtherCategory, $this->model->getDataMap(DataCategoryHashMap::class, 2));
    }

    /**
     * Tests getDataMap with exception
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
