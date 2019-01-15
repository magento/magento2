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
     * @var ThemePackageInfo
     */
    private $themePackageInfo;

    /**
     * @var Remove
     */
    private $remove;

    /**
     * @var ThemeProvider
     */
    private $themeProvider;

    /**
     * Constructor
     *
     * @param ThemePackageInfo $themePackageInfo
     * @param Remove $remove
     * @param ThemeProvider $themeProvider
     */
    public function __construct(ThemePackageInfo $themePackageInfo, Remove $remove, ThemeProvider $themeProvider)
    {
        $this->themePackageInfo = $themePackageInfo;
        $this->remove = $remove;
        $this->themeProvider = $themeProvider;
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
