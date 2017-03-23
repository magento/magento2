<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Cron\Helper;

use Magento\Setup\Model\Cron\Helper\ThemeUninstall;

class ThemeUninstallTest extends \PHPUnit_Framework_TestCase
{
    public function testUninstall()
    {
        $themeUninstaller = $this->getMock(\Magento\Theme\Model\Theme\ThemeUninstaller::class, [], [], '', false);
        $themePackageInfo = $this->getMock(\Magento\Theme\Model\Theme\ThemePackageInfo::class, [], [], '', false);
        $output =
            $this->getMockForAbstractClass(\Symfony\Component\Console\Output\OutputInterface::class, [], '', false);
        $themePackageInfo->expects($this->once())->method('getFullThemePath')->willReturn('theme/path');
        $themeUninstaller->expects($this->once())->method('uninstallRegistry')->with($output, ['theme/path']);
        $themeUninstall = new ThemeUninstall($themeUninstaller, $themePackageInfo);
        $themeUninstall->uninstall($output, 'vendor/package-theme');
    }
}
