<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron\Helper;

use Magento\Theme\Model\Theme\ThemePackageInfo;
use Magento\Theme\Model\Theme\ThemeUninstaller;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Helper class for JobComponentUninstall to uninstall a theme component
 * @since 2.0.0
 */
class ThemeUninstall
{
    /**
     * @var ThemeUninstaller
     * @since 2.0.0
     */
    private $themeUninstaller;

    /**
     * @var ThemePackageInfo
     * @since 2.0.0
     */
    private $themePackageInfo;

    /**
     * Constructor
     *
     * @param ThemeUninstaller $themeUninstaller
     * @param ThemePackageInfo $themePackageInfo
     * @since 2.0.0
     */
    public function __construct(ThemeUninstaller $themeUninstaller, ThemePackageInfo $themePackageInfo)
    {
        $this->themeUninstaller = $themeUninstaller;
        $this->themePackageInfo = $themePackageInfo;
    }

    /**
     * Perform setup side uninstall
     *
     * @param OutputInterface $output
     * @param string $componentName
     * @return void
     * @since 2.0.0
     */
    public function uninstall(OutputInterface $output, $componentName)
    {
        $themePath = $this->themePackageInfo->getFullThemePath($componentName);
        $this->themeUninstaller->uninstallRegistry($output, [$themePath]);
    }
}
