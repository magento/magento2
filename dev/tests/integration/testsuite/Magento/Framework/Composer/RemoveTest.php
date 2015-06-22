<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Composer;

use Magento\Framework\App\Filesystem\DirectoryList;

class RemoveTest extends \PHPUnit_Framework_TestCase
{
    public function testRemove()
    {
        $composerApp = $this->getMock(
            'Composer\Console\Application',
            ['setAutoExit', 'resetComposer', 'run'],
            [],
            '',
            false
        );
        $directoryList = $this->getMock('Magento\Framework\App\Filesystem\DirectoryList', [], [], '', false);
        $directoryList->expects($this->once())->method('getRoot');
        $directoryList->expects($this->once())
            ->method('getPath')
            ->with(DirectoryList::CONFIG)
            ->willReturn(BP . '/app/etc');
        $composerApp->expects($this->once())->method('setAutoExit')->with(false);
        $composerApp->expects($this->once())->method('run');
        $remove = new Remove($composerApp, $directoryList);
        $remove->remove(['magento/package-a', 'magento/package-b']);
    }
}
