<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config\Test\Unit\Dom;

use Magento\Framework\Config\Dom\ArrayNodeConfig;
use Magento\Framework\Config\Dom\NodePathMatcher;

/**
 * Test for
 *
 * @see ArrayNodeConfig
 */
class ArrayNodeConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ArrayNodeConfig
     */
    protected $object;

    /**
     * @var NodePathMatcher|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $nodePathMatcher;

    protected function setUp(): void
    {
        $this->nodePathMatcher = $this->createMock(NodePathMatcher::class);
        $this->object = new ArrayNodeConfig(
            $this->nodePathMatcher,
            ['/root/assoc/one' => 'name', '/root/assoc/two' => 'id', '/root/assoc/three' => 'key'],
            ['/root/numeric/one', '/root/numeric/two', '/root/numeric/three']
        );
    }

    public function testIsNumericArrayMatched()
    {
        $xpath = '/root/numeric[@attr="value"]/two';
        $this->nodePathMatcher->expects(
            $this->at(0)
        )->method(
            'match'
        )->with(
            '/root/numeric/one',
            $xpath
        )->willReturn(
            false
        );
        $this->nodePathMatcher->expects(
            $this->at(1)
        )->method(
            'match'
        )->with(
            '/root/numeric/two',
            $xpath
        )->willReturn(
            true
        );
        $this->assertTrue($this->object->isNumericArray($xpath));
    }

    public function testIsNumericArrayNotMatched()
    {
        $xpath = '/root/numeric[@attr="value"]/four';
        $this->nodePathMatcher->expects(
            $this->at(0)
        )->method(
            'match'
        )->with(
            '/root/numeric/one',
            $xpath
        )->willReturn(
            false
        );
        $this->nodePathMatcher->expects(
            $this->at(1)
        )->method(
            'match'
        )->with(
            '/root/numeric/two',
            $xpath
        )->willReturn(
            false
        );
        $this->nodePathMatcher->expects(
            $this->at(2)
        )->method(
            'match'
        )->with(
            '/root/numeric/three',
            $xpath
        )->willReturn(
            false
        );
        $this->assertFalse($this->object->isNumericArray($xpath));
    }

    public function testGetAssocArrayKeyAttributeMatched()
    {
        $xpath = '/root/assoc[@attr="value"]/two';
        $this->nodePathMatcher->expects(
            $this->at(0)
        )->method(
            'match'
        )->with(
            '/root/assoc/one',
            $xpath
        )->willReturn(
            false
        );
        $this->nodePathMatcher->expects(
            $this->at(1)
        )->method(
            'match'
        )->with(
            '/root/assoc/two',
            $xpath
        )->willReturn(
            true
        );
        $this->assertEquals('id', $this->object->getAssocArrayKeyAttribute($xpath));
    }

    public function testGetAssocArrayKeyAttributeNotMatched()
    {
        $xpath = '/root/assoc[@attr="value"]/four';
        $this->nodePathMatcher->expects(
            $this->at(0)
        )->method(
            'match'
        )->with(
            '/root/assoc/one',
            $xpath
        )->willReturn(
            false
        );
        $this->nodePathMatcher->expects(
            $this->at(1)
        )->method(
            'match'
        )->with(
            '/root/assoc/two',
            $xpath
        )->willReturn(
            false
        );
        $this->nodePathMatcher->expects(
            $this->at(2)
        )->method(
            'match'
        )->with(
            '/root/assoc/three',
            $xpath
        )->willReturn(
            false
        );
        $this->assertNull($this->object->getAssocArrayKeyAttribute($xpath));
    }
}
