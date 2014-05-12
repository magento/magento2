<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Database transaction events manager
 */
namespace Magento\TestFramework\Event;

class Transaction
{
    /**
     * @var \Magento\TestFramework\EventManager
     */
    protected $_eventManager;

    /**
     * @var \Magento\TestFramework\Event\Param\Transaction
     */
    protected $_eventParam;

    /**
     * @var bool
     */
    protected $_isTransactionActive = false;

    /**
     * Constructor
     *
     * @param \Magento\TestFramework\EventManager $eventManager
     */
    public function __construct(\Magento\TestFramework\EventManager $eventManager)
    {
        $this->_eventManager = $eventManager;
    }

    /**
     * Handler for 'startTest' event
     *
     * @param \PHPUnit_Framework_TestCase $test
     */
    public function startTest(\PHPUnit_Framework_TestCase $test)
    {
        $this->_processTransactionRequests('startTest', $test);
    }

    /**
     * Handler for 'endTest' event
     *
     * @param \PHPUnit_Framework_TestCase $test
     */
    public function endTest(\PHPUnit_Framework_TestCase $test)
    {
        $this->_processTransactionRequests('endTest', $test);
    }

    /**
     * Handler for 'endTestSuite' event
     */
    public function endTestSuite()
    {
        $this->_rollbackTransaction();
    }

    /**
     * Query whether there are any requests for transaction operations and performs them
     *
     * @param string $eventName
     * @param \PHPUnit_Framework_TestCase $test
     */
    protected function _processTransactionRequests($eventName, \PHPUnit_Framework_TestCase $test)
    {
        $param = $this->_getEventParam();
        $this->_eventManager->fireEvent($eventName . 'TransactionRequest', array($test, $param));
        if ($param->isTransactionRollbackRequested()) {
            $this->_rollbackTransaction();
        }
        if ($param->isTransactionStartRequested()) {
            $this->_startTransaction($test);
        }
    }

    /**
     * Start transaction and fire 'startTransaction' event
     *
     * @param \PHPUnit_Framework_TestCase $test
     */
    protected function _startTransaction(\PHPUnit_Framework_TestCase $test)
    {
        if (!$this->_isTransactionActive) {
            $this->_getAdapter()->beginTransparentTransaction();
            $this->_isTransactionActive = true;
            $this->_eventManager->fireEvent('startTransaction', array($test));
        }
    }

    /**
     * Rollback transaction and fire 'rollbackTransaction' event
     */
    protected function _rollbackTransaction()
    {
        if ($this->_isTransactionActive) {
            $this->_getAdapter()->rollbackTransparentTransaction();
            $this->_isTransactionActive = false;
            $this->_eventManager->fireEvent('rollbackTransaction');
        }
    }

    /**
     * Retrieve database adapter instance
     *
     * @param string $connectionName 'read' or 'write'
     * @return \Magento\Framework\DB\Adapter\AdapterInterface|\Magento\TestFramework\Db\Adapter\TransactionInterface
     * @throws \Magento\Framework\Exception
     */
    protected function _getAdapter($connectionName = 'core_write')
    {
        /** @var $resource \Magento\Framework\App\Resource */
        $resource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\App\Resource');
        return $resource->getConnection($connectionName);
    }

    /**
     * Retrieve clean instance of transaction event parameter
     *
     * @return \Magento\TestFramework\Event\Param\Transaction
     */
    protected function _getEventParam()
    {
        /* reset object state instead of instantiating new object over and over again */
        if (!$this->_eventParam) {
            $this->_eventParam = new \Magento\TestFramework\Event\Param\Transaction();
        } else {
            $this->_eventParam->__construct();
        }
        return $this->_eventParam;
    }
}
