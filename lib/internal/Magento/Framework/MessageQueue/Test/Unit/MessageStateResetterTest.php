<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit;

use InvalidArgumentException;
use Magento\Framework\MessageQueue\MessageStateResetter;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use PHPUnit\Framework\TestCase;

class MessageStateResetterTest extends TestCase
{
    public function testResetStateResetsEveryService(): void
    {
        $first = $this->createMock(ResetAfterRequestInterface::class);
        $second = $this->createMock(ResetAfterRequestInterface::class);
        $first->expects($this->once())->method('_resetState');
        $second->expects($this->once())->method('_resetState');

        (new MessageStateResetter(['first' => $first, 'second' => $second]))->resetState();
    }

    public function testConstructorRejectsServiceWithoutResetState(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Service "invalid" must implement');

        new MessageStateResetter(['invalid' => new \stdClass()]);
    }
}
