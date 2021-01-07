<?php
/**
 * Implementation of the magentoApiDataFixture DocBlock annotation.
 *
 * The difference of magentoApiDataFixture from magentoDataFixture is
 * that no transactions should be used for API data fixtures.
 * Otherwise fixture data will not be accessible to Web API functional tests.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Annotation;

use Magento\Customer\Model\Metadata\AttributeMetadataCache;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Implementation of the @magentoApiDataFixture DocBlock annotation.
 */
class ApiDataFixture extends DataFixture
{
    public const ANNOTATION = 'magentoApiDataFixture';

    /**
     * Handler for 'startTest' event
     *
     * @param TestCase $test
     */
    public function startTest(TestCase $test)
    {
        Bootstrap::getInstance()->reinitialize();
        /** Apply method level fixtures if thy are available, apply class level fixtures otherwise */
        $this->_applyFixtures(
            $this->_getFixtures($test, 'method') ?: $this->_getFixtures($test, 'class'),
            $test
        );
    }

    /**
     * Handler for 'endTest' event
     *
     * @param TestCase $test
     */
    public function endTest(TestCase $test)
    {
        $this->_revertFixtures($test);
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(AttributeMetadataCache::class)->clean();
    }

    /**
     * @inheritdoc
     */
    protected function getAnnotation(): string
    {
        return self::ANNOTATION;
    }
}
