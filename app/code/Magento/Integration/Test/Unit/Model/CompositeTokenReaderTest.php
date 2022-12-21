<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Model;

use Magento\Integration\Api\Data\UserToken;
use Magento\Integration\Api\Exception\UserTokenException;
use Magento\Integration\Api\UserTokenReaderInterface;
use Magento\Integration\Model\CompositeTokenReader;
use PHPUnit\Framework\TestCase;

class CompositeTokenReaderTest extends TestCase
{
    public function testCompositeReaderReturnsFirstToken()
    {
        $token1 = $this->createMock(UserToken::class);
        $reader1 = $this->createMock(UserTokenReaderInterface::class);
        $reader1->method('read')
            ->with('abc')
            ->willReturn($token1);

        $token2 = $this->createMock(UserToken::class);
        $reader2 = $this->createMock(UserTokenReaderInterface::class);
        $reader2->method('read')
            ->with('abc')
            ->willReturn($token2);

        $composite = new CompositeTokenReader([$reader1, $reader2]);

        self::assertSame($token1, $composite->read('abc'));
    }

    public function testCompositeReaderReturnsNextTokenOnError()
    {
        $reader1 = $this->createMock(UserTokenReaderInterface::class);
        $reader1->method('read')
            ->with('abc')
            ->willThrowException(new UserTokenException('Fail'));

        $token2 = $this->createMock(UserToken::class);
        $reader2 = $this->createMock(UserTokenReaderInterface::class);
        $reader2->method('read')
            ->with('abc')
            ->willReturn($token2);

        $composite = new CompositeTokenReader([$reader1, $reader1, $reader2]);

        self::assertSame($token2, $composite->read('abc'));
    }

    public function testCompositeReaderFailsWhenNoTokensFound()
    {
        $this->expectExceptionMessage('Composite reader could not read a token');
        $this->expectException(UserTokenException::class);

        $reader1 = $this->createMock(UserTokenReaderInterface::class);
        $reader1->method('read')
            ->with('abc')
            ->willThrowException(new UserTokenException('Fail'));

        $composite = new CompositeTokenReader([$reader1, $reader1, $reader1]);
        $composite->read('abc');
    }
}
