<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Composer;

use Magento\Framework\App\Filesystem\DirectoryList;

class RemoveTest extends \PHPUnit_Framework_TestCase
{
    public function testRemove()
    {
        $composerAppFactory = $this->getMock(
            'Magento\Framework\Composer\MagentoComposerApplicationFactory',
            [],
            [],
            '',
            false
        );

        $composerApp = $this->getMock(
            'Magento\Composer\MagentoComposerApplication',
            [],
            [],
            '',
            false
        );

        $composerApp->expects($this->once())->method('runComposerCommand');

        $composerAppFactory->expects($this->once())->method('create')->willReturn($composerApp);

        $remove = new Remove($composerAppFactory);
        $remove->remove(['magento/package-a', 'magento/package-b']);
    }
}
