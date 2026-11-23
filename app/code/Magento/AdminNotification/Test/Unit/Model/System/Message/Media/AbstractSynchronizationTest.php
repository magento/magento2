<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AdminNotification\Test\Unit\Model\System\Message\Media;

use Magento\AdminNotification\Model\System\Message\Media\Synchronization\Error;
use Magento\AdminNotification\Model\System\Message\Media\Synchronization\Success;
use Magento\MediaStorage\Model\File\Storage\Flag;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AbstractSynchronizationTest extends TestCase
{
    /**
     * @var Flag&MockObject
     */
    private Flag $flag;

    protected function setUp(): void
    {
        $this->flag = $this->createPartialMock(Flag::class, ['loadSelf', 'save', 'getFlagData']);
    }

    public function testConstructorDoesNotLoadFlag(): void
    {
        $this->flag->expects($this->never())->method('loadSelf');

        new Error($this->flag);
        new Success($this->flag);
    }

    public function testSharedFlagIsLoadedOnceForAllMessages(): void
    {
        $this->flag->expects($this->once())
            ->method('loadSelf')
            ->willReturnCallback(fn () => $this->flag->setOrigData());
        $this->flag->expects($this->never())->method('save');

        $error = new Error($this->flag);
        $success = new Success($this->flag);

        $this->assertFalse($error->isDisplayed());
        $this->assertFalse($success->isDisplayed());
    }

    public function testMessagesReadFlagDataLoadedByFirstMessage(): void
    {
        $this->flag->expects($this->once())
            ->method('loadSelf')
            ->willReturnCallback(
                fn () => $this->flag->setState(Flag::STATE_FINISHED)
                    ->setOrigData()
            );
        $this->flag->method('getFlagData')->willReturn(['has_errors' => true]);
        $this->flag->expects($this->once())->method('save');

        $error = new Error($this->flag);
        $success = new Success($this->flag);

        $this->assertTrue($error->isDisplayed());
        $this->assertFalse($success->isDisplayed());
        $this->assertSame(Flag::STATE_NOTIFIED, $this->flag->getState());
    }
}
