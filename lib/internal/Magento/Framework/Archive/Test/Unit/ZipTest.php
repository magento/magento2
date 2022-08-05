<?php declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Archive\Test\Unit;

use Magento\Framework\Archive\Zip;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ZipTest extends TestCase
{

    /**
     * @var Zip|MockObject
     */
    protected $zip;

    protected function setUp(): void
    {
        $this->zip = $this->getMockBuilder(Zip::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Check constructor if no exceptions is thrown.
     */
    public function testConstructorNoExceptions()
    {
        try {
            $reflectedClass = new \ReflectionClass(Zip::class);
            $constructor = $reflectedClass->getConstructor();
            $constructor->invoke($this->zip, []);
        } catch (\Exception $e) {
            $this->fail('Failed asserting that no exceptions is thrown');
        }
    }

    /**
     * @depends testConstructorNoExceptions
     */
    public function testPack()
    {
        $this->markTestSkipped('Method pack contains dependency on \ZipArchive object');
    }

    /**
     * @depends testConstructorNoExceptions
     */
    public function testUnpack()
    {
        $this->markTestSkipped('Method unpack contains dependency on \ZipArchive object');
    }
}
