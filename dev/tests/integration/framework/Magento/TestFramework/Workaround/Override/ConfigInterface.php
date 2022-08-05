<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Workaround\Override;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Config\Reader\Filesystem;
use Magento\Framework\View\File\Collector\Decorator\ModuleDependency;
use Magento\Framework\View\File\Collector\Decorator\ModuleOutput;
use Magento\TestFramework\Workaround\Override\Config\Converter;
use Magento\TestFramework\Workaround\Override\Config\FileCollector;
use Magento\TestFramework\Workaround\Override\Config\FileResolver;
use Magento\TestFramework\Workaround\Override\Config\Dom;
use Magento\TestFramework\Workaround\Override\Config\SchemaLocator;
use Magento\TestFramework\Workaround\Override\Config\ValidationState;
use PHPUnit\Framework\TestCase;

/**
 * Provides integration tests configuration.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
interface ConfigInterface
{
    /**
     * Returns an array with skip key and skipMessage key if test is skipped.
     *
     * @param TestCase $test
     * @return array
     */
    public function getSkipConfiguration(TestCase $test): array;

    /**
     * Test has configuration flag.
     *
     * @param string $className
     * @return bool
     */
    public function hasSkippedTest(string $className): bool;

    /**
     * Get config from class node
     *
     * @param TestCase $test
     * @param string|null $fixtureType
     * @return array
     */
    public function getClassConfig(TestCase $test, ?string $fixtureType = null): array;

    /**
     * Get config from method node
     *
     * @param TestCase $test
     * @param string|null $fixtureType
     * @return array
     */
    public function getMethodConfig(TestCase $test, ?string $fixtureType = null): array;

    /**
     * Get config from dataSet node
     *
     * @param TestCase $test
     * @param string|null $fixtureType
     * @return array
     */
    public function getDataSetConfig(TestCase $test, ?string $fixtureType = null): array;
}
