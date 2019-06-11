<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Listener of PHPUnit built-in events
 */
namespace Magento\TestFramework\Event;

class PhpUnit implements \PHPUnit\Framework\TestListener
{
    use \PHPUnit\Framework\TestListenerDefaultImplementation;

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
     */
    public function startTestSuite(\PHPUnit\Framework\TestSuite $suite): void
    {
        /* PHPUnit runs tests with data provider in own test suite for each test, so just skip such test suites */
        if ($suite instanceof \PHPUnit\Framework\DataProviderTestSuite) {
            return;
        }
        $this->_eventManager->fireEvent('startTestSuite');
    }

    /**
     * {@inheritdoc}
     */
    public function endTestSuite(\PHPUnit\Framework\TestSuite $suite): void
    {
        if ($suite instanceof \PHPUnit\Framework\DataProviderTestSuite) {
            return;
        }
        $this->_eventManager->fireEvent('endTestSuite', [$suite], true);
    }

    /**
     * {@inheritdoc}
     */
    public function startTest(\PHPUnit\Framework\Test $test): void
    {
        if (!$test instanceof \PHPUnit\Framework\TestCase || $test instanceof \PHPUnit\Framework\Warning) {
            return;
        }
        $this->_eventManager->fireEvent('startTest', [$test]);
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function endTest(\PHPUnit\Framework\Test $test, $time): void
    {
        if (!$test instanceof \PHPUnit\Framework\TestCase || $test instanceof \PHPUnit\Framework\Warning) {
            return;
        }
        $this->_eventManager->fireEvent('endTest', [$test], true);
    }
}
