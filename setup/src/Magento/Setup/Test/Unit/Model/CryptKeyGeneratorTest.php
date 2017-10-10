<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use Magento\Framework\Math\Random;
use Magento\Setup\Model\CryptKeyGenerator;
use PHPUnit\Framework\TestCase;

/**
 * Testcase for CryptKeyGenerator
 */
class CryptKeyGeneratorTest extends TestCase
{
    /**
     * @var Random|\PHPUnit_Framework_MockObject_MockObject
     */
    private $randomMock;

    /**
     * @var CryptKeyGenerator
     */
    private $cryptKeyGenerator;

    public function setUp()
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
            ->method('getRandomString')
            ->willReturn('');
        
        $this->cryptKeyGenerator->generate();
    }

    public function testReturnsMd5OfRandomString()
    {
        $expected = 'fdb7594e77f1ad5fbb8e6c917b6012ce'; // == 'magento2'

        $this->randomMock
            ->method('getRandomString')
            ->willReturn('magento2');

        $actual = $this->cryptKeyGenerator->generate();

        $this->assertEquals($expected, $actual);
    }
}
