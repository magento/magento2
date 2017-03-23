<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Listener of PHPUnit built-in events
 */
namespace Magento\TestFramework\Event;

class PhpUnit implements \PHPUnit_Framework_TestListener
{
    /**
     * Used when PHPUnit framework instantiates the class on its own and passes nothing to the constructor
     *
     * @var \Magento\TestFramework\EventManager
     */
    protected static $_defaultEventManager;

    /**
     * @var \Magento\TestFramework\EventManager
     */
    protected $_eventManager;

    /**
     * Assign default event manager instance
     *
     * @param \Magento\TestFramework\EventManager $eventManager
     */
    public static function setDefaultEventManager(\Magento\TestFramework\EventManager $eventManager = null)
    {
        self::$_defaultEventManager = $eventManager;
    }

    /**
     * Constructor
     *
     * @param \Magento\TestFramework\EventManager $eventManager
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(\Magento\TestFramework\EventManager $eventManager = null)
    {
        $this->_eventManager = $eventManager ?: self::$_defaultEventManager;
        if (!$this->_eventManager) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Instance of the event manager is required.'));
        }
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ShortVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addError(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ShortVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addFailure(\PHPUnit_Framework_Test $test, \PHPUnit_Framework_AssertionFailedError $e, $time)
    {
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ShortVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addIncompleteTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ShortVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addRiskyTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ShortVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addSkippedTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function startTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        /* PHPUnit runs tests with data provider in own test suite for each test, so just skip such test suites */
        if ($suite instanceof \PHPUnit_Framework_TestSuite_DataProvider) {
            return;
        }
        $this->_eventManager->fireEvent('startTestSuite');
    }

    /**
     * {@inheritdoc}
     */
    public function endTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        if ($suite instanceof \PHPUnit_Framework_TestSuite_DataProvider) {
            return;
        }
        $this->_eventManager->fireEvent('endTestSuite', [$suite], true);
    }

    /**
     * {@inheritdoc}
     */
    public function startTest(\PHPUnit_Framework_Test $test)
    {
        if (!$test instanceof \PHPUnit_Framework_TestCase || $test instanceof \PHPUnit_Framework_Warning) {
            return;
        }
        $this->_eventManager->fireEvent('startTest', [$test]);
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function endTest(\PHPUnit_Framework_Test $test, $time)
    {
        if (!$test instanceof \PHPUnit_Framework_TestCase || $test instanceof \PHPUnit_Framework_Warning) {
            return;
        }
        $this->_eventManager->fireEvent('endTest', [$test], true);
    }
}
