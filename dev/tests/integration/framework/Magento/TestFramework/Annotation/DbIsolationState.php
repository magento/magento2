<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Annotation;

use Magento\TestFramework\Fixture\ParserInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class DbIsolationState
{
    /**
     * Returns the db isolation state
     *
     * @param TestCase $test
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getState(TestCase $test): array
    {
        $annotations = TestCaseAnnotation::getInstance()->getAnnotations($test);
        $parser = Bootstrap::getObjectManager()->create(\Magento\TestFramework\Fixture\Parser\DbIsolation::class);
        $converter = static fn ($stateInfo) => $stateInfo['enabled'] ? 'enabled' : 'disabled';
        $classDbIsolationState =  array_map($converter, $parser->parse($test, ParserInterface::SCOPE_CLASS))
            ?: ($annotations['class'][DbIsolation::MAGENTO_DB_ISOLATION] ?? []);
        $methodDbIsolationState =  array_map($converter, $parser->parse($test, ParserInterface::SCOPE_METHOD))
            ?: ($annotations['method'][DbIsolation::MAGENTO_DB_ISOLATION] ?? []);
        return $methodDbIsolationState ?: $classDbIsolationState;
    }
}
