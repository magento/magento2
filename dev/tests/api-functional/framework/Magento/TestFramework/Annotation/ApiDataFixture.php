<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Annotation;

use Magento\Customer\Model\Metadata\AttributeMetadataCache;
use Magento\TestFramework\Event\Param\Transaction;
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
    public function startTestTransactionRequest(TestCase $test, Transaction $param): void
    {
        Bootstrap::getInstance()->reinitialize();
        parent::startTestTransactionRequest($test, $param);
    }

    /**
     * @inheritdoc
     */
    public function endTestTransactionRequest(TestCase $test, Transaction $param): void
    {
        parent::endTestTransactionRequest($test, $param);
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(AttributeMetadataCache::class)->clean();
    }

    /**
     * @inheritdoc
     */
    protected function getParsers(): array
    {
        $parsers = [];
        // Add magentoDataFixture annotations
        $parsers[] = Bootstrap::getObjectManager()->create(
            \Magento\TestFramework\Annotation\Parser\DataFixture::class,
            ['annotation' => DataFixture::ANNOTATION]
        );
        return array_merge(
            parent::getParsers(),
            $parsers
        );
    }
}
