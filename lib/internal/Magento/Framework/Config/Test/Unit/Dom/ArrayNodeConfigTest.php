<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Config\Test\Unit\Dom;

use Magento\Framework\Config\Dom\ArrayNodeConfig;
use Magento\Framework\Config\Dom\NodePathMatcher;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for
 *
 * @see ArrayNodeConfig
 */
class ArrayNodeConfigTest extends TestCase
{
    /**
     * @var ArrayNodeConfig
     */
    protected $object;

    /**
     * @var NodePathMatcher|MockObject
     */
    protected $nodePathMatcher;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->nodePathMatcher = $this->createMock(NodePathMatcher::class);
        $this->object = new ArrayNodeConfig(
            $this->nodePathMatcher,
            ['/root/assoc/one' => 'name', '/root/assoc/two' => 'id', '/root/assoc/three' => 'key'],
            ['/root/numeric/one', '/root/numeric/two', '/root/numeric/three']
        );
    }

    /**
     * @return void
     */
    public function testIsNumericArrayMatched(): void
    {
        $xpath = '/root/numeric[@attr="value"]/two';
        $this->nodePathMatcher
            ->method('match')
            ->willReturnCallback(
                function ($arg1, $arg2) use ($xpath) {
                    if ($arg1 == '/root/numeric/one' && $arg2 == $xpath) {
                        return false;
                    } elseif ($arg1 == '/root/numeric/two' && $arg2 == $xpath) {
                        return true;
                    }
                }
            );
        $this->assertTrue($this->object->isNumericArray($xpath));
    }

    /**
     * @return void
     */
    public function testIsNumericArrayNotMatched(): void
    {
        $xpath = '/root/numeric[@attr="value"]/four';
        $this->nodePathMatcher
            ->method('match')
            ->willReturnCallback(
                function ($arg1, $arg2) use ($xpath) {
                    if ($arg1 == '/root/numeric/one' && $arg2 == $xpath) {
                        return false;
                    } elseif ($arg1 == '/root/numeric/two' && $arg2 == $xpath) {
                        return false;
                    } elseif ($arg1 == '/root/numeric/three' && $arg2 == $xpath) {
                        return false;
                    }
                }
            );
        $this->assertFalse($this->object->isNumericArray($xpath));
    }

    /**
     * @return void
     */
    public function testGetAssocArrayKeyAttributeMatched(): void
    {
        $xpath = '/root/assoc[@attr="value"]/two';
        $this->nodePathMatcher
            ->method('match')
            ->willReturnCallback(
                function ($arg1, $arg2) use ($xpath) {
                    if ($arg1 == '/root/assoc/one' && $arg2 == $xpath) {
                        return false;
                    } elseif ($arg1 == '/root/assoc/two' && $arg2 == $xpath) {
                        return true;
                    }
                }
            );
        $this->assertEquals('id', $this->object->getAssocArrayKeyAttribute($xpath));
    }

    /**
     * @return void
     */
    public function testGetAssocArrayKeyAttributeNotMatched(): void
    {
        $xpath = '/root/assoc[@attr="value"]/four';
        $this->nodePathMatcher
            ->method('match')
            ->willReturnCallback(
                function ($arg1, $arg2) use ($xpath) {
                    if ($arg1 == '/root/assoc/one' && $arg2 == $xpath) {
                        return false;
                    } elseif ($arg1 == '/root/assoc/two' && $arg2 == $xpath) {
                        return false;
                    } elseif ($arg1 == '/root/assoc/three' && $arg2 == $xpath) {
                        return false;
                    }
                }
            );
        $this->assertNull($this->object->getAssocArrayKeyAttribute($xpath));
    }
}
