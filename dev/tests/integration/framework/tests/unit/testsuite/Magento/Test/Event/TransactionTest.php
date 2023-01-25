<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\TestFramework\Event\Transaction.
 */
namespace Magento\Test\Event;

class TransactionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\Event\Transaction|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_object;

    /**
     * @var \Magento\TestFramework\EventManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_eventManager;

    /**
     * @var \Magento\TestFramework\Db\Adapter\TransactionInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_adapter;

    protected function setUp(): void
    {
        $this->_eventManager = $this->getMockBuilder(\Magento\TestFramework\EventManager::class)
            ->onlyMethods(['fireEvent'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->_adapter =
            $this->createPartialMock(\Magento\TestFramework\Db\Adapter\Mysql::class, ['beginTransaction', 'rollBack']);
        $this->_object = $this->getMockBuilder(\Magento\TestFramework\Event\Transaction::class)
            ->onlyMethods(['_getConnection'])
            ->setConstructorArgs([$this->_eventManager])
            ->getMock();

        $this->_object->expects($this->any())->method('_getConnection')->willReturn($this->_adapter);
    }

    /**
     * Imitate transaction start request
     *
     * @param string $eventName
     */
    protected function _imitateTransactionStartRequest($eventName)
    {
        $callback = function ($eventName, array $parameters) {
            /** @var $param \Magento\TestFramework\Event\Param\Transaction */
            $param = $parameters[1];
            $param->requestTransactionStart();
        };
        $this->_eventManager
            ->method('fireEvent')
            ->withConsecutive([$eventName])
            ->willReturnOnConsecutiveCalls($this->returnCallback($callback));
    }

    /**
     * Setup expectations for "transaction start" use case.
     */
    protected function _expectTransactionStart()
    {
        $this->_adapter->expects($this->once())->method('beginTransaction');
    }

    /**
     * Imitate transaction rollback request
     *
     * @param string $eventName
     */
    protected function _imitateTransactionRollbackRequest($eventName)
    {
        $callback = function ($eventName, array $parameters) {
            /** @var $param \Magento\TestFramework\Event\Param\Transaction */
            $param = $parameters[1];
            $param->requestTransactionRollback();
        };
        $this->_eventManager
            ->method('fireEvent')
            ->withConsecutive([$eventName])
            ->willReturnOnConsecutiveCalls($this->returnCallback($callback));
    }

    /**
     * Setup expectations for "transaction rollback" use case.
     */
    protected function _expectTransactionRollback()
    {
        $this->_adapter->expects($this->once())->method('rollback');
    }

    /**
     * @param string $method
     * @param string $eventName
     * @dataProvider startAndRollbackTransactionDataProvider
     */
    public function testStartAndRollbackTransaction($method, $eventName)
    {
        $eventManagerWithArgs = [];
        $this->_imitateTransactionStartRequest($eventName);
        $this->_expectTransactionStart();
        $eventManagerWithArgs[] = ['startTransaction'];
        $this->_object->{$method}($this);

        $this->_imitateTransactionRollbackRequest($eventName);
        $this->_expectTransactionRollback();
        $eventManagerWithArgs[] = ['rollbackTransaction'];
        $this->_object->{$method}($this);

        $this->_eventManager
            ->method('fireEvent')
            ->withConsecutive($eventManagerWithArgs);
    }

    public function startAndRollbackTransactionDataProvider()
    {
        return [
            'method "startTest"' => ['startTest', 'startTestTransactionRequest'],
            'method "endTest"' => ['endTest', 'endTestTransactionRequest']
        ];
    }

    /**
     * @param string $method
     * @param string $eventName
     * @dataProvider startAndRollbackTransactionDataProvider
     */
    public function testDoNotStartAndRollbackTransaction($method, $eventName)
    {
        $this->_eventManager->expects($this->once())->method('fireEvent')->with($eventName);
        $this->_adapter->expects($this->never())->method($this->anything());
        $this->_object->{$method}($this);
    }

    public function testEndTestSuiteDoNothing()
    {
        $this->_eventManager->expects($this->never())->method('fireEvent');
        $this->_adapter->expects($this->never())->method($this->anything());
        $this->_object->endTestSuite();
    }

    public function testEndTestSuiteRollbackTransaction()
    {
        $this->_imitateTransactionStartRequest('startTestTransactionRequest');
        $this->_object->startTest($this);

        $this->_expectTransactionRollback();
        $this->_eventManager
            ->method('fireEvent')
            ->withConsecutive(['rollbackTransaction']);

        $this->_object->endTestSuite();
    }
}
