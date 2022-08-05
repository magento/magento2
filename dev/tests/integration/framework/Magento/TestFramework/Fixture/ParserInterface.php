<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture;

use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\TestCase;

/**
 * Fixtures parser interface
 */
interface ParserInterface
{
    public const SCOPE_CLASS = 'class';

    public const SCOPE_METHOD = 'method';

    /**
     * Returns fixtures configuration defined in the given scope
     *
     * @param TestCase $testCase
     * @param string $scope
     * @return mixed
     * @throws LocalizedException
     */
    public function parse(TestCase $testCase, string $scope);
}
