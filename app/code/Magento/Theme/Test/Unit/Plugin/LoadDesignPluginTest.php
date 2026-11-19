<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Plugin;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Config\Dom\ValidationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Phrase;
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

        $designLoaderMock->expects($this->once())->method('loadDesign');
        $designLoaderMock->expects($this->never())->method('load');
        $plugin->beforeExecute($actionMock);
    }

    public function testBeforeExecuteAddsMessageOnInvalidDesignConfig()
    {
        /** @var MockObject|ActionInterface $actionMock */
        $actionMock = $this->createMock(Action::class);

        /** @var MockObject|DesignLoader $designLoaderMock */
        $designLoaderMock = $this->createMock(DesignLoader::class);
        $designLoaderMock->expects($this->once())
            ->method('loadDesign')
            ->willThrowException(
                new LocalizedException(new Phrase('Invalid design config'), new ValidationException('Invalid XML'))
            );

        /** @var MockObject|MessageInterface $messageMock */
        $messageMock = $this->createMock(MessageInterface::class);
        $messageMock->expects($this->once())->method('setText')->with('Invalid design config')->willReturnSelf();

        /** @var MockObject|ManagerInterface $messageManagerMock */
        $messageManagerMock = $this->createMock(ManagerInterface::class);
        $messageManagerMock->expects($this->once())
            ->method('createMessage')
            ->with(MessageInterface::TYPE_ERROR)
            ->willReturn($messageMock);
        $messageManagerMock->expects($this->once())->method('addUniqueMessages')->with([$messageMock]);

        $plugin = new LoadDesignPlugin($designLoaderMock, $messageManagerMock);

        $plugin->beforeExecute($actionMock);
    }
}
