<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Fixture path resolver for file based data fixture
 */
class LegacyDataFixturePathResolver
{
    /**
     * @var ComponentRegistrarInterface
     */
    private $componentRegistrar;

    /**
     * @param ComponentRegistrarInterface $componentRegistrar
     */
    public function __construct(
        ComponentRegistrarInterface $componentRegistrar
    ) {
        $this->componentRegistrar = $componentRegistrar;
    }

    /**
     * Get the full path to the fixture
     *
     * @param string $fixture
     * @return string
     * @throws LocalizedException
     */
    public function resolve(string $fixture): string
    {
        if ($this->isModuleAnnotation($fixture)) {
            $filePath = $this->getModulePath($fixture);
        } else {
            $filePath = INTEGRATION_TESTS_DIR . '/testsuite/' . $fixture;
        }

        return $filePath;
    }

    /**
     * Check if the fixture file is located in the module path
     *
     * @param string $fixture
     * @return bool
     */
    private function isModuleAnnotation(string $fixture): bool
    {
        return strpos($fixture, '::') !== false;
    }

    /**
     * Get the full path to the fixture in the module
     *
     * @param string $fixture
     * @return string
     * @throws LocalizedException
     */
    private function getModulePath(string $fixture): string
    {
        [$moduleName, $fixtureFile] = explode('::', $fixture, 2);

        $modulePath = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName);

        if ($modulePath === null) {
            throw new LocalizedException(__('Can\'t find registered Module with name %1 .', $moduleName));
        }

        return $modulePath . '/' . ltrim($fixtureFile, '/');
    }
}
