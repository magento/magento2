<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\TestFramework\Event\Transaction.
 */
namespace Magento\Test\Event;

class TransactionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Event\Transaction|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_object;

    /**
     * @var \Magento\TestFramework\EventManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventManager;

    /**
     * @var \Magento\TestFramework\Db\Adapter\TransactionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_adapter;

    protected function setUp()
    {
        $this->_eventManager = $this->getMock(
            'Magento\TestFramework\EventManager',
            ['fireEvent'],
            [[]]
        );
        $this->_adapter = $this->getMock(
            'Magento\TestFramework\Db\Adapter\TransactionInterface',
            ['beginTransparentTransaction', 'commitTransparentTransaction', 'rollbackTransparentTransaction']
        );
        $this->_object = $this->getMock(
            'Magento\TestFramework\Event\Transaction',
            ['_getAdapter'],
            [$this->_eventManager]
        );
        $this->_object->expects($this->any())->method('_getAdapter')->will($this->returnValue($this->_adapter));
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
        $this->_eventManager->expects(
            $this->at(0)
        )->method(
            'fireEvent'
        )->with(
            $eventName
        )->will(
            $this->returnCallback($callback)
        );
    }

    /**
     * Setup expectations for "transaction start" use case
     *
     * @param \PHPUnit_Framework_MockObject_Matcher_Invocation $invocationMatcher
     */
    protected function _expectTransactionStart(\PHPUnit_Framework_MockObject_Matcher_Invocation $invocationMatcher)
    {
        $this->_eventManager->expects($invocationMatcher)->method('fireEvent')->with('startTransaction');
        $this->_adapter->expects($this->once())->method('beginTransparentTransaction');
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
        $this->_eventManager->expects(
            $this->at(0)
        )->method(
            'fireEvent'
        )->with(
            $eventName
        )->will(
            $this->returnCallback($callback)
        );
    }

    /**
     * Setup expectations for "transaction rollback" use case
     *
     * @param \PHPUnit_Framework_MockObject_Matcher_Invocation $invocationMatcher
     */
    protected function _expectTransactionRollback(\PHPUnit_Framework_MockObject_Matcher_Invocation $invocationMatcher)
    {
        $this->_eventManager->expects($invocationMatcher)->method('fireEvent')->with('rollbackTransaction');
        $this->_adapter->expects($this->once())->method('rollbackTransparentTransaction');
    }

    /**
     * @param string $method
     * @param string $eventName
     * @dataProvider startAndRollbackTransactionDataProvider
     */
    public function testStartAndRollbackTransaction($method, $eventName)
    {
        $this->_imitateTransactionStartRequest($eventName);
        $this->_expectTransactionStart($this->at(1));
        $this->_object->{$method}($this);

        $this->_imitateTransactionRollbackRequest($eventName);
        $this->_expectTransactionRollback($this->at(1));
        $this->_object->{$method}($this);
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

        $this->_expectTransactionRollback($this->once());
        $this->_object->endTestSuite();
    }
}
