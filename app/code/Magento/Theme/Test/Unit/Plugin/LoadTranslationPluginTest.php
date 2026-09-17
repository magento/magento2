<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Plugin;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\DesignLoader;
use Magento\Theme\Plugin\LoadTranslationPlugin;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LoadTranslationPluginTest extends TestCase
{
    public function testBeforeExecute(): void
    {
        /** @var MockObject|ActionInterface $actionMock */
        $actionMock = $this->createMock(Action::class);

        /** @var MockObject|DesignLoader $designLoaderMock */
        $designLoaderMock = $this->createMock(DesignLoader::class);

        /** @var MockObject|ManagerInterface $messageManagerMock */
        $messageManagerMock = $this->createMock(ManagerInterface::class);

        $plugin = new LoadTranslationPlugin($designLoaderMock, $messageManagerMock);

        $designLoaderMock->expects($this->once())->method('loadTranslation');
        $designLoaderMock->expects($this->never())->method('load');
        $plugin->beforeExecute($actionMock);
    }
}
