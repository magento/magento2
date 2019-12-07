<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Cron\Helper;

use Magento\Setup\Model\Cron\Helper\ThemeUninstall;

class ThemeUninstallTest extends \PHPUnit\Framework\TestCase
{
    public function testUninstall()
    {
        $themeUninstaller = $this->createMock(\Magento\Theme\Model\Theme\ThemeUninstaller::class);
        $themePackageInfo = $this->createMock(\Magento\Theme\Model\Theme\ThemePackageInfo::class);
        $output =
            $this->getMockForAbstractClass(\Symfony\Component\Console\Output\OutputInterface::class, [], '', false);
        $themePackageInfo->expects($this->once())->method('getFullThemePath')->willReturn('theme/path');
        $themeUninstaller->expects($this->once())->method('uninstallRegistry')->with($output, ['theme/path']);
        $themeUninstall = new ThemeUninstall($themeUninstaller, $themePackageInfo);
        $themeUninstall->uninstall($output, 'vendor/package-theme');
    }
}
