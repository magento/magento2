<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Composer\DependencyChecker;
use Magento\Theme\Model\Theme\ThemeDependencyChecker;

/**
 * Class checks components dependencies for uninstall flow
 */
class UninstallDependencyCheck
{
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
     */
    public function __construct(
        ComposerInformation $composerInfo,
        DependencyChecker $dependencyChecker,
        ThemeDependencyCheckerFactory $themeDependencyCheckerFactory
    ) {
        $this->composerInfo = $composerInfo;
        $this->packageDependencyChecker = $dependencyChecker;
        $this->themeDependencyChecker = $themeDependencyCheckerFactory->create();
    }

    /**
     * Run Composer dependency check for uninstall
     *
     * @param array $packages
     * @return array
     * @throws \RuntimeException
     */
    public function runUninstallReadinessCheck(array $packages)
    {
        try {
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
        } catch (\RuntimeException $e) {
            $message = str_replace(PHP_EOL, '<br/>', htmlspecialchars($e->getMessage()));
            return ['success' => false, 'error' => $message];
        }
    }
}
