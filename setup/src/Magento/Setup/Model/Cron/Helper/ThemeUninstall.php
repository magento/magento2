<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron\Helper;

use Magento\Theme\Model\Theme\ThemePackageInfo;
use Magento\Theme\Model\Theme\ThemeUninstaller;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Helper class for JobComponentUninstall to uninstall a theme component
 */
class ThemeUninstall
{
    /**
     * @var ThemeUninstaller
     */
    private $themeUninstaller;

    /**
     * @var ThemePackageInfo
     */
    private $themePackageInfo;

    /**
     * Constructor
     *
     * @param ThemeUninstaller $themeUninstaller
     * @param ThemePackageInfo $themePackageInfo
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
     */
    public function uninstall(OutputInterface $output, $componentName)
    {
        $themePath = $this->themePackageInfo->getFullThemePath($componentName);
        $this->themeUninstaller->uninstallRegistry($output, [$themePath]);
    }
}
