<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
