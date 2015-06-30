<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Test\Unit\Model\Resource;

class TaxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Weee\Model\Resource\Tax
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapterMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->storeManagerMock = $this->getMock('\Magento\Store\Model\StoreManagerInterface');

        $this->selectMock = $this->getMock('\Magento\Framework\DB\Select', [], [], '', false);

        $this->adapterMock = $this->getMock('\Magento\Framework\DB\Adapter\AdapterInterface', [], [], '', false);
        $this->adapterMock->expects($this->once())
            ->method('select')
            ->willReturn($this->selectMock);

        $this->resourceMock = $this->getMock('\Magento\Framework\App\Resource', [], [], '', false);
        $this->resourceMock->expects($this->at(0))
            ->method('getConnection')
            ->with('core_write')
            ->willReturn($this->adapterMock);

        $this->resourceMock->expects($this->at(1))
            ->method('getConnection')
            ->with('core_read')
            ->willReturn($this->adapterMock);

        $this->resourceMock->expects($this->once())
            ->method('getTableName')
            ->willReturn('table_name');

        $contextMock = $this->getMock('\Magento\Framework\Model\Resource\Db\Context', [], [], '', false);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->resourceMock);

        $this->model = $this->objectManager->getObject(
            'Magento\Weee\Model\Resource\Tax',
            [
                'context' => $contextMock,
            ]
        );
    }

    public function testInWeeeLocation()
    {
        $this->selectMock->expects($this->at(1))
            ->method('where')
            ->with('website_id IN(?)', [1, 0])
            ->willReturn($this->selectMock);

        $this->selectMock->expects($this->at(2))
            ->method('where')
            ->with('country = ?', 'US')
            ->willReturn($this->selectMock);

        $this->selectMock->expects($this->at(3))
            ->method('where')
            ->with('state = ?', 0)
            ->willReturn($this->selectMock);

        $this->selectMock->expects($this->any())
            ->method('from')
            ->with('table_name', 'value')
            ->willReturn($this->selectMock);

        $this->model->isWeeeInLocation('US', 0, 1);
    }
}
