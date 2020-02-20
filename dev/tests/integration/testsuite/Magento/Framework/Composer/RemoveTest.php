<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Composer;

use Magento\Composer\MagentoComposerApplication;

class RemoveTest extends \PHPUnit\Framework\TestCase
{
    public function testRemove()
    {
        $composerAppFactory = $this->getMockBuilder(MagentoComposerApplicationFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $composerApp = $this->getMockBuilder(MagentoComposerApplication::class)
            ->disableOriginalConstructor()
            ->getMock();

        $composerApp->expects($this->once())
            ->method('runComposerCommand')
            ->with(
                [
                    'command' => 'remove',
                    'packages' => ['magento/package-a', 'magento/package-b'],
                    '--no-update-with-dependencies' => true,
                ]
            );
        $composerAppFactory->expects($this->once())
            ->method('create')
            ->willReturn($composerApp);

        $remove = new Remove($composerAppFactory);
        $remove->remove(['magento/package-a', 'magento/package-b']);
    }
}
