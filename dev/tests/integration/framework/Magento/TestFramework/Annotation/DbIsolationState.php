<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Annotation;

use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Fixture\ParserInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class DbIsolationState
{
    /**
     * Returns the db isolation state
     *
     * @param TestCase $test
     * @return bool|null
     * @throws LocalizedException
     */
    public function isEnabled(TestCase $test): ?bool
    {
        $objectManager = Bootstrap::getObjectManager();
        $parsers = $objectManager
            ->create(
                \Magento\TestFramework\Annotation\Parser\Composite::class,
                [
                    'parsers' => [
                        $objectManager->get(\Magento\TestFramework\Annotation\Parser\DbIsolation::class),
                        $objectManager->get(\Magento\TestFramework\Fixture\Parser\DbIsolation::class)
                    ]
                ]
            );
        $values = $parsers->parse($test, ParserInterface::SCOPE_METHOD)
            ?: $parsers->parse($test, ParserInterface::SCOPE_CLASS);

        if (count($values) > 1) {
            throw new LocalizedException(
                __('Only one "@%1" annotation is allowed per test', DbIsolation::MAGENTO_DB_ISOLATION)
            );
        }
        return $values[0]['enabled'] ?? null;
    }
}
