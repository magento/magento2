<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Layer\Filter;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class PriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);

        $contextMock = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceMock = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->resourceMock);
        $this->model = $objectManagerHelper->getObject(
            \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price::class,
            [
                'context' => $contextMock
            ]
        );
    }

    public function testGetMainTable()
    {
        $expectedTableName = 'expectedTableName';
        $this->resourceMock->expects($this->once())->method('getTableName')->willReturn($expectedTableName);
        $this->assertEquals($expectedTableName, $this->model->getMainTable());
    }
}
