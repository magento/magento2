<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Theme;

use Magento\Framework\Composer\Remove;
use Symfony\Component\Console\Output\OutputInterface;

class ThemeUninstaller
{
    /**
     * Constructor
     *
     * @param ThemePackageInfo $themePackageInfo
     * @param Remove $remove
     * @param ThemeProvider $themeProvider
     */
    public function __construct(
        private readonly ThemePackageInfo $themePackageInfo,
        private readonly Remove $remove,
        private readonly ThemeProvider $themeProvider
    ) {
    }

    /**
     * Uninstall theme from database registry
     *
     * @param OutputInterface $output
     * @param array $themePaths
     * @return void
     */
    public function uninstallRegistry(OutputInterface $output, array $themePaths)
    {
        $output->writeln('<info>Removing ' . implode(', ', $themePaths) . ' from database');
        foreach ($themePaths as $themePath) {
            $this->themeProvider->getThemeByFullPath($themePath)->delete();
        }
    }

    /**
     * Uninstall theme from code base
     *
     * @param OutputInterface $output
     * @param array $themePaths
     * @return void
     */
    public function uninstallCode(OutputInterface $output, array $themePaths)
    {
        $output->writeln('<info>Removing ' . implode(', ', $themePaths) . ' from Magento codebase');
        $packageNames = [];
        foreach ($themePaths as $themePath) {
            $packageNames[] = $this->themePackageInfo->getPackageName($themePath);
        }
        $output->writeln($this->remove->remove($packageNames));
    }
}
