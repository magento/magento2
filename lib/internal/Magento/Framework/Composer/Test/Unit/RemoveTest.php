<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Composer\Test\Unit;

use Magento\Composer\MagentoComposerApplication;
use Magento\Framework\Composer\MagentoComposerApplicationFactory;
use Magento\Framework\Composer\Remove;
use PHPUnit\Framework\TestCase;

class RemoveTest extends TestCase
{
    public function testRemoveRunsComposerNonInteractively(): void
    {
        $composerApplication = $this->createMock(MagentoComposerApplication::class);
        $composerApplication->expects($this->once())
            ->method('runComposerCommand')
            ->with(
                [
                    'command' => 'remove',
                    'packages' => ['vendor/module-one', 'vendor/module-two'],
                    '--no-update-with-dependencies' => true,
                    '--no-interaction' => true,
                ]
            )
            ->willReturn('composer output');
        $composerApplicationFactory = $this->createStub(MagentoComposerApplicationFactory::class);
        $composerApplicationFactory->method('create')->willReturn($composerApplication);

        $remove = new Remove($composerApplicationFactory);

        $this->assertSame('composer output', $remove->remove(['vendor/module-one', 'vendor/module-two']));
    }
}
