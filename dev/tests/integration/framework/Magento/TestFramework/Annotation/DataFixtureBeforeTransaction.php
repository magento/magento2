<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Annotation;

use PHPUnit\Framework\TestCase;

/**
 * Implementation of the @magentoDataFixtureBeforeTransaction DocBlock annotation
 */
class DataFixtureBeforeTransaction extends AbstractDataFixture
{
    public const ANNOTATION = 'magentoDataFixtureBeforeTransaction';

    /**
     * Handler for 'startTest' event
     *
     * @param TestCase $test
     */
    public function startTest(TestCase $test)
    {
        $fixtures = $this->_getFixtures($test);
        if ($fixtures) {
            $this->_applyFixtures($fixtures);
        }
    }

    /**
     * Handler for 'endTest' event
     *
     * @param TestCase $test
     */
    public function endTest(TestCase $test)
    {
        /* Isolate other tests from test-specific fixtures */
        if ($this->_appliedFixtures && $this->_getFixtures($test)) {
            $this->_revertFixtures();
        }
    }

    /**
     * @inheritdoc
     */
    protected function getAnnotation(): string
    {
        return self::ANNOTATION;
    }
}
