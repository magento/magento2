<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Composer\Test\Unit;

use Magento\Framework\Composer\Remove;

class RemoveTest extends \PHPUnit_Framework_TestCase
{
    public function testRemove()
    {
        $composerApp = $this->getMock('Composer\Console\Application', [], [], '', false);
        $directoryList = $this->getMock('Magento\Framework\App\Filesystem\DirectoryList', [], [], '', false);
        $directoryList->expects($this->once())->method('getRoot');
        $composerApp->expects($this->once())->method('setAutoExit')->with(false);
        $composerApp->expects($this->once())->method('run');
        $remove = new Remove($composerApp, $directoryList);
        $remove->remove(['magento/package-a', 'magento/package-b']);
    }
}
