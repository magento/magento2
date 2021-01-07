<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Workaround\Override\Fixture;

use PHPUnit\Framework\TestCase;

/**
 * Class determines fixture applying according to configurations
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
interface ResolverInterface
{
    /**
     * Set current test to instance
     *
     * @param TestCase $currentTest
     * @return void
     */
    public function setCurrentTest(?TestCase $currentTest): void;

    /**
     * Get current test
     *
     * @return TestCase|null
     */
    public function getCurrentTest(): ?TestCase;

    /**
     * Set which fixture type is executed
     *
     * @param null|string $fixtureType
     * @return void
     */
    public function setCurrentFixtureType(?string $fixtureType): void;

    /**
     * Require fixture wrapper
     *
     * @param string $path
     * @return void
     */
    public function requireDataFixture(string $path): void;

    /**
     * Apply override configurations to config fixtures list
     *
     * @param TestCase $test
     * @param array $fixtures
     * @param string $fixtureType
     * @return array
     */
    public function applyConfigFixtures(TestCase $test, array $fixtures, string $fixtureType): array;

    /**
     * Apply override configurations to data fixtures list
     *
     * @param TestCase $test
     * @param array $fixtures
     * @param string $fixtureType
     * @return array
     */
    public function applyDataFixtures(TestCase $test, array $fixtures, string $fixtureType): array;
}
