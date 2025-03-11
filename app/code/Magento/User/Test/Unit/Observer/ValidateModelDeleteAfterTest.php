<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\User\Test\Unit\Observer;

use Magento\Framework\Event;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\User\Observer\ValidateModelDeleteAfter;
use Magento\User\Model\User;
use PHPUnit\Framework\MockObject\MockObject;

class ValidateModelDeleteAfterTest extends TestCase
{
    /** @var ValidateModelDeleteAfter */
    private $observer;

    /** @var ManagerInterface|MockObject */
    private $eventManagerMock;

    protected function setUp(): void
    {
        $this->eventManagerMock = $this->createMock(ManagerInterface::class);
        $this->observer = new ValidateModelDeleteAfter($this->eventManagerMock);
    }

    public function testExecute()
    {
        $deletedUser = $this->createMock(User::class);
        $userModel = $this->createMock(User::class);
        $deletedUser->method('getData')->willReturn(['key' => 'value']);
        $observerData = [
            'deletedUser' => $deletedUser,
            'model' => $userModel,
        ];
        $eventMock = $this->createMock(Event::class);
        $eventMock->method('getData')->willReturnCallback(function ($key) use ($observerData) {
            return $observerData[$key] ?? null;
        });
        $observer = $this->createMock(Observer::class);
        $observer->method('getEvent')->willReturn($eventMock);
        $userModel->expects($this->once())
            ->method('setData')
            ->with(['key' => 'value'])
            ->willReturnSelf();
        $userModel->method('getData')->willReturn(['key' => 'value']);
        $userModel->expects($this->once())
            ->method('getOrigData')
            ->willReturn(null);
        $userModel->expects($this->once())
            ->method('setOrigData')
            ->with('key', 'value')
            ->willReturnSelf();
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with('model_delete_after', ['object' => $userModel]);
        $this->observer->execute($observer);
    }
}
