<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Annotation;

use Magento\TestFramework\Event\Param\Transaction;
use PHPUnit\Framework\TestCase;

/**
 * Implementation of the @magentoDataFixture DocBlock annotation.
 */
class DataFixture extends AbstractDataFixture
{
    public const ANNOTATION = 'magentoDataFixture';

    /**
     * Handler for 'startTestTransactionRequest' event
     *
     * @param TestCase $test
     * @param Transaction $param
     * @return void
     */
    public function startTestTransactionRequest(TestCase $test, Transaction $param): void
    {
        $fixtures = $this->_getFixtures($test);
        /* Start transaction before applying first fixture to be able to revert them all further */
        if ($fixtures) {
            if ($this->getDbIsolationState($test) !== ['disabled']) {
                $param->requestTransactionStart();
            } else {
                $this->_applyFixtures($fixtures, $test);
            }
        }
    }

    /**
     * Handler for 'endTestNeedTransactionRollback' event
     *
     * @param TestCase $test
     * @param Transaction $param
     * @return void
     */
    public function endTestTransactionRequest(TestCase $test, Transaction $param): void
    {
        /* Isolate other tests from test-specific fixtures */
        if ($this->_appliedFixtures && $this->_getFixtures($test)) {
            if ($this->getDbIsolationState($test) !== ['disabled']) {
                $param->requestTransactionRollback();
            } else {
                $this->_revertFixtures($test);
            }
        }
    }

    /**
     * Handler for 'startTransaction' event
     *
     * @param TestCase $test
     * @return void
     */
    public function startTransaction(TestCase $test): void
    {
        $this->_applyFixtures($this->_getFixtures($test), $test);
    }

    /**
     * Handler for 'rollbackTransaction' event
     *
     * @param TestCase $test
     * @return void
     */
    public function rollbackTransaction(): void
    {
        $this->_revertFixtures();
    }

    /**
     * @inheritdoc
     */
    protected function getAnnotation(): string
    {
        return self::ANNOTATION;
    }
}
