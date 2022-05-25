<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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
        $this->_applyFixtures($this->_getFixtures($test), $test);
    }

    /**
     * Handler for 'endTest' event
     *
     * @param TestCase $test
     */
    public function endTest(TestCase $test)
    {
        $this->_revertFixtures($test);
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
    protected function getDbIsolationState(TestCase $test)
    {
        return parent::getDbIsolationState($test) ?: ['disabled'];
    }

    /**
     * @inheritdoc
     */
    protected function _applyFixtures(array $fixtures, TestCase $test)
    {
        Bootstrap::getInstance()->reinitialize();
        parent::_applyFixtures($fixtures, $test);
    }

    /**
     * @inheritdoc
     */
    protected function _revertFixtures(?TestCase $test = null)
    {
        parent::_revertFixtures($test);
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(AttributeMetadataCache::class)->clean();
    }
}
