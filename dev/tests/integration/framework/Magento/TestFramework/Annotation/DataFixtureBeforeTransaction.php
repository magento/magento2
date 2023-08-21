<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Annotation;

use Magento\TestFramework\Helper\Bootstrap;
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
            $this->_applyFixtures($fixtures, $test);
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
            $this->_revertFixtures($test);
        }
    }

    /**
     * @inheritdoc
     */
    protected function getAnnotation(): string
    {
        return self::ANNOTATION;
    }

    /**
     * @inheritdoc
     */
    protected function getParsers(): array
    {
        $parsers = [];
        $parsers[] = Bootstrap::getObjectManager()->create(
            \Magento\TestFramework\Fixture\Parser\DataFixture::class,
            [
                'attributeClass' => \Magento\TestFramework\Fixture\DataFixtureBeforeTransaction::class
            ]
        );
        return array_merge(
            parent::getParsers(),
            $parsers
        );
    }
}
