<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Theme;

use Magento\Framework\Composer\AbstractComponentUninstaller;
use Magento\Framework\Composer\Remove;
use Symfony\Component\Console\Output\OutputInterface;

class ThemeUninstaller extends AbstractComponentUninstaller
{
    /**#@+
     * Theme uninstall options
     */
    const OPTION_UNINSTALL_REGISTRY = 'registry';
    const OPTION_UNINSTALL_CODE = 'code';
    /**#@-*/

    /**
     * @var PackageNameFinder
     */
    private $packageNameFinder;

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
     * @param PackageNameFinder $packageNameFinder
     * @param Remove $remove
     * @param ThemeProvider $themeProvider
     */
    public function __construct(PackageNameFinder $packageNameFinder, Remove $remove, ThemeProvider $themeProvider)
    {
        $this->packageNameFinder = $packageNameFinder;
        $this->remove = $remove;
        $this->themeProvider = $themeProvider;
    }

    /**
     * Uninstall a theme
     *
     * @param OutputInterface $output
     * @param array $components
     * @param array $option
     * @return void
     */
    public function uninstall(OutputInterface $output, array $components, array $option)
    {
        if (isset($option[self::OPTION_UNINSTALL_REGISTRY]) && $option[self::OPTION_UNINSTALL_REGISTRY]) {
            $output->writeln('<info>Removing ' . implode(', ', $components) . ' from database');
            foreach ($components as $component) {
                $this->themeProvider->getThemeByFullPath($component)->delete();
            }
        }

        if (isset($option[self::OPTION_UNINSTALL_CODE]) && $option[self::OPTION_UNINSTALL_CODE]) {
            $output->writeln('<info>Removing ' . implode(', ', $components) . ' from Magento codebase');
            $packageNames = [];
            foreach ($components as $component) {
                $packageNames[] = $this->packageNameFinder->getPackageName($component);
            }
            $output->writeln($this->remove->remove($packageNames));
        }
    }
}
