<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\TestFramework\Unit\Listener;

use PHPUnit\Framework\Test;
use PHPUnit\Framework\Warning;

/**
 * Listener of PHPUnit built-in events that enforces cleanup of cyclic object references
 *
 */
class GarbageCleanup implements \PHPUnit\Framework\TestListener
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function startTestSuite(\PHPUnit\Framework\TestSuite $suite)
    {
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function endTestSuite(\PHPUnit\Framework\TestSuite $suite)
    {
        gc_collect_cycles();
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function startTest(\PHPUnit\Framework\Test $test)
    {
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function endTest(\PHPUnit\Framework\Test $test, $time)
    {
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addWarning(Test $test, Warning $e, $time)
    {
    }
}
