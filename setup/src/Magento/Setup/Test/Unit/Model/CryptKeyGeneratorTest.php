<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model;

use Magento\Framework\Math\Random;
use Magento\Setup\Model\CryptKeyGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Testcase for CryptKeyGenerator
 */
class CryptKeyGeneratorTest extends TestCase
{
    /**
     * @var Random|MockObject
     */
    private $randomMock;

    /**
     * @var CryptKeyGenerator
     */
    private $cryptKeyGenerator;

    protected function setUp(): void
    {
        $this->randomMock = $this->getMockBuilder(Random::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cryptKeyGenerator = new CryptKeyGenerator($this->randomMock);
    }

    public function testStringForHashingIsReadFromRandom()
    {
        $this->randomMock
            ->expects($this->once())
            ->method('getRandomBytes')
            ->willReturn('');

        $this->cryptKeyGenerator->generate();
    }
}
