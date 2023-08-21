<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Plugin;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\DesignLoader;
use Magento\Theme\Plugin\LoadDesignPlugin;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LoadDesignPluginTest extends TestCase
{
    public function testBeforeExecute()
    {
        /** @var MockObject|ActionInterface $actionMock */
        $actionMock = $this->createMock(Action::class);

        /** @var MockObject|DesignLoader $designLoaderMock */
        $designLoaderMock = $this->createMock(DesignLoader::class);

        /** @var MockObject|ManagerInterface $messageManagerMock */
        $messageManagerMock = $this->createMock(ManagerInterface::class);

        $plugin = new LoadDesignPlugin($designLoaderMock, $messageManagerMock);

        $designLoaderMock->expects($this->once())->method('load');
        $plugin->beforeExecute($actionMock);
    }
}
