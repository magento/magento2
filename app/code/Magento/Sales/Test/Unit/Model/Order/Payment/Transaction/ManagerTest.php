<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Order\Payment\Transaction;


use Magento\Sales\Model\Order\Payment\Transaction;

/**
 * Class ManagerTest
 */
class ManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction\Manager
     */
    private $manager;

    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction\Repository | \PHPUnit_Framework_MockObject_MockObject
     */
    private $repositoryMock;

    /**
     * Init
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->repositoryMock = $this->getMock(
            'Magento\Sales\Model\Order\Payment\Transaction\Repository',
            [],
            [],
            '',
            false
        );
        $this->manager = $objectManager->getObject(
            'Magento\Sales\Model\Order\Payment\Transaction\Manager',
            ['transactionRepository' => $this->repositoryMock]
        );
    }

    /**
     * @dataProvider getAuthorizationDataProvider
     * @param $parentTransactionId
     * @param $paymentId
     * @param $orderId
     */
    public function testGetAuthorizationTransaction($parentTransactionId, $paymentId, $orderId)
    {
        $transaction = $this->getMock(
            'Magento\Sales\Model\Order\Payment\Transaction',
            [],
            [],
            '',
            false
        );
        if ($parentTransactionId) {
            $this->repositoryMock->expects($this->once())->method('getByTxnId')->with(
                $parentTransactionId,
                $paymentId,
                $orderId
            )->willReturn($transaction);
        } else {
            $this->repositoryMock->expects($this->once())->method('getByTxnType')->with(
                Transaction::TYPE_AUTH,
                $paymentId,
                $orderId
            )->willReturn($transaction);
        }
        $this->assertEquals(
            $transaction,
            $this->manager->getAuthorizationTransaction($parentTransactionId, $paymentId, $orderId)
        );
    }

    public function getAuthorizationDataProvider()
    {
        return [
            'withParentId' => [false, 1, 1],
            'withoutParentId' => [1, 2, 1]
        ];
    }
}
