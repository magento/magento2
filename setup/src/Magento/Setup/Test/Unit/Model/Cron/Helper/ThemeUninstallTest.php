<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model\Cron\Helper;

use Magento\Setup\Model\Cron\Helper\ThemeUninstall;
use Magento\Theme\Model\Theme\ThemePackageInfo;
use Magento\Theme\Model\Theme\ThemeUninstaller;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

class ThemeUninstallTest extends TestCase
{
    public function testUninstall()
    {
        $themeUninstaller = $this->createMock(ThemeUninstaller::class);
        $themePackageInfo = $this->createMock(ThemePackageInfo::class);
        $output =
            $this->getMockForAbstractClass(OutputInterface::class, [], '', false);
        $themePackageInfo->expects($this->once())->method('getFullThemePath')->willReturn('theme/path');
        $themeUninstaller->expects($this->once())->method('uninstallRegistry')->with($output, ['theme/path']);
        $themeUninstall = new ThemeUninstall($themeUninstaller, $themePackageInfo);
        $themeUninstall->uninstall($output, 'vendor/package-theme');
    }
}
