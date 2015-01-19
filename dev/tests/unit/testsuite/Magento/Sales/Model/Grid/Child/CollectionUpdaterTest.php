<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Grid\Child;

class CollectionUpdaterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Grid\Child\CollectionUpdater
     */
    protected $collectionUpdater;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    protected function setUp()
    {
        $this->registryMock = $this->getMock('Magento\Framework\Registry', [], [], '', false);

        $this->collectionUpdater = new \Magento\Sales\Model\Grid\Child\CollectionUpdater(
            $this->registryMock
        );
    }

    public function testUpdateIfOrderExists()
    {
        $collectionMock = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Payment\Transaction\Collection', [], [], '', false
        );
        $transactionMock = $this->getMock('Magento\Sales\Model\Order\Payment\Transaction', [], [], '', false);
        $this->registryMock
            ->expects($this->once())
            ->method('registry')
            ->with('current_transaction')
            ->will($this->returnValue($transactionMock));
        $transactionMock->expects($this->once())->method('getId')->will($this->returnValue('transactionId'));
        $collectionMock->expects($this->once())->method('addParentIdFilter')->will($this->returnSelf());
        $this->assertEquals($collectionMock, $this->collectionUpdater->update($collectionMock));
    }
}
