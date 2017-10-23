<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\ErrorLog;

use Magento\TestFramework\Helper;

class Listener implements \PHPUnit\Framework\TestListener
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ShortVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addError(\PHPUnit\Framework\Test $test, \Exception $e, $time)
    {
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ShortVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addFailure(\PHPUnit\Framework\Test $test, \PHPUnit\Framework\AssertionFailedError $e, $time)
    {
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ShortVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addIncompleteTest(\PHPUnit\Framework\Test $test, \Exception $e, $time)
    {
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ShortVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addRiskyTest(\PHPUnit\Framework\Test $test, \Exception $e, $time)
    {
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ShortVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addSkippedTest(\PHPUnit\Framework\Test $test, \Exception $e, $time)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function startTestSuite(\PHPUnit\Framework\TestSuite $suite)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function endTestSuite(\PHPUnit\Framework\TestSuite $suite)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function startTest(\PHPUnit\Framework\Test $test)
    {
        $this->logger = Helper\Bootstrap::getObjectManager()->get(\Magento\TestFramework\ErrorLog\Logger::class);
        $this->logger->clearMessages();
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function endTest(\PHPUnit\Framework\Test $test, $time)
    {
        if ($test instanceof \PHPUnit\Framework\TestCase) {
            $messages = $this->logger->getMessages();
            try {
                if ($messages) {
                    $test->assertEquals(
                        '',
                        var_export($messages, true),
                        'Errors were added to log during test execution.'
                    );
                }
            } catch (\Exception $e) {
                $test->getTestResultObject()->addError($test, $e, 0);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addWarning(\PHPUnit\Framework\Test $test, \PHPUnit\Framework\Warning $e, $time)
    {
    }
}
