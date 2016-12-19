<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Map;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\CatalogUrlRewrite\Model\Map\DataMapPool;
use Magento\CatalogUrlRewrite\Model\Map\DataCategoryMap;
use Magento\CatalogUrlRewrite\Model\Map\DataProductMap;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class DataMapPoolTest
 */
class DataMapPoolTest extends \PHPUnit_Framework_TestCase
{
    /** @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $objectManagerMock;

    /** @var DataMapPool|\PHPUnit_Framework_MockObject_MockObject */
    private $model;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMock(ObjectManagerInterface::class);

        $this->model = (new ObjectManager($this))->getObject(
            DataMapPool::class,
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
        $dataCategoryMapMock = $this->getMock(DataCategoryMap::class, [], [], '', false);
        $dataProductMapMock = $this->getMock(DataProductMap::class, [], [], '', false);
        $dataProductMapMockOtherCategory = $this->getMock(DataProductMap::class, [], [], '', false);

        $this->objectManagerMock->expects($this->any())
            ->method('create')
            ->willReturnOnConsecutiveCalls($dataCategoryMapMock, $dataProductMapMock, $dataProductMapMockOtherCategory);
        $this->assertEquals($dataCategoryMapMock, $this->model->getDataMap(DataCategoryMap::class, 1));
        $this->assertEquals($dataCategoryMapMock, $this->model->getDataMap(DataCategoryMap::class, 1));
        $this->assertEquals($dataProductMapMock, $this->model->getDataMap(DataProductMap::class, 1));
        $this->assertEquals($dataProductMapMockOtherCategory, $this->model->getDataMap(DataCategoryMap::class, 2));
    }
}
