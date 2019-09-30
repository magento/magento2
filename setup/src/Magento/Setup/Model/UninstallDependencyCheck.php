<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Composer\DependencyChecker;
use Magento\Framework\Exception\RuntimeException;
use Magento\Theme\Model\Theme\ThemeDependencyChecker;
use Magento\Framework\Escaper;
use Magento\Framework\App\ObjectManager;

/**
 * Class checks components dependencies for uninstall flow
 */
class UninstallDependencyCheck
{
    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var ComposerInformation
     */
    private $composerInfo;

    /**
     * @var DependencyChecker
     */
    private $packageDependencyChecker;

    /**
     * Theme Dependency Checker
     *
     * @var ThemeDependencyChecker
     */
    private $themeDependencyChecker;

    /**
     * Constructor
     *
     * @param ComposerInformation $composerInfo
     * @param DependencyChecker $dependencyChecker
     * @param ThemeDependencyCheckerFactory $themeDependencyCheckerFactory
     * @param Escaper|null $escaper
     */
    public function __construct(
        ComposerInformation $composerInfo,
        DependencyChecker $dependencyChecker,
        ThemeDependencyCheckerFactory $themeDependencyCheckerFactory,
        Escaper $escaper = null
    ) {
        $this->composerInfo = $composerInfo;
        $this->packageDependencyChecker = $dependencyChecker;
        $this->themeDependencyChecker = $themeDependencyCheckerFactory->create();
        $this->escaper = $escaper ?? ObjectManager::getInstance()->get(
            Escaper::class
        );
    }

    /**
     * Run Composer dependency check for uninstall
     *
     * @param array $packages
     * @return array
     */
    public function runUninstallReadinessCheck(array $packages)
    {
        try {
            return $this->checkForMissingDependencies($packages);
        } catch (\RuntimeException $e) {
            $message = str_replace(PHP_EOL, '<br/>', $this->escaper->escapeHtml($e->getMessage()));
            return ['success' => false, 'error' => $message];
        }
    }

    /**
     * Check for missing dependencies
     *
     * @param array $packages
     * @return array
     * @throws \RuntimeException
     */
    private function checkForMissingDependencies(array $packages)
    {
        $packagesAndTypes = $this->composerInfo->getRootRequiredPackageTypesByName();
        $dependencies = $this->packageDependencyChecker->checkDependencies($packages, true);
        $messages = [];
        $themes = [];

        foreach ($packages as $package) {
            if (!isset($packagesAndTypes[$package])) {
                throw new \RuntimeException('Package ' . $package . ' not found in the system.');
            }

            switch ($packagesAndTypes[$package]) {
                case ComposerInformation::METAPACKAGE_PACKAGE_TYPE:
                    unset($dependencies[$package]);
                    break;
                case ComposerInformation::THEME_PACKAGE_TYPE:
                    $themes[] = $package;
                    break;
            }

            if (!empty($dependencies[$package])) {
                $messages[] = $package . " has the following dependent package(s): "
                    . implode(', ', $dependencies[$package]);
            }
        }

        if (!empty($themes)) {
            $messages = array_merge(
                $messages,
                $this->themeDependencyChecker->checkChildThemeByPackagesName($themes)
            );
        }

        if (!empty($messages)) {
            throw new \RuntimeException(implode(PHP_EOL, $messages));
        }

        return ['success' => true];
    }
}
