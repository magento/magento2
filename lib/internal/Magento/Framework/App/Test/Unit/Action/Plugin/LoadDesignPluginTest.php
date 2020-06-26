<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Action\Plugin;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Plugin\LoadDesignPlugin;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\DesignLoader;
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
        $messageManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);

        $plugin = new LoadDesignPlugin($designLoaderMock, $messageManagerMock);

        $designLoaderMock->expects($this->once())->method('load');
        $plugin->beforeExecute($actionMock);
    }
}
