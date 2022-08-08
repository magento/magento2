<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Config\Test\Unit\Dom;

use Magento\Framework\Config\Dom\NodeMergingConfig;
use Magento\Framework\Config\Dom\NodePathMatcher;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for
 *
 * @see NodeMergingConfig
 */
class NodeMergingConfigTest extends TestCase
{
    /**
     * @var NodeMergingConfig
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
        $this->object = new NodeMergingConfig(
            $this->nodePathMatcher,
            ['/root/one' => 'name', '/root/two' => 'id', '/root/three' => 'key']
        );
    }

    /**
     * @return void
     */
    public function testGetIdAttributeMatched(): void
    {
        $xpath = '/root/two[@attr="value"]';
        $this->nodePathMatcher
            ->method('match')
            ->withConsecutive(
                ['/root/one', $xpath],
                ['/root/two', $xpath]
            )
            ->willReturnOnConsecutiveCalls(false, true);
        $this->assertEquals('id', $this->object->getIdAttribute($xpath));
    }

    /**
     * @return void
     */
    public function testGetIdAttributeNotMatched(): void
    {
        $xpath = '/root/four[@attr="value"]';
        $this->nodePathMatcher
            ->method('match')
            ->withConsecutive(
                ['/root/one', $xpath],
                ['/root/two', $xpath],
                ['/root/three', $xpath]
            )
            ->willReturnOnConsecutiveCalls(false, false, false);
        $this->assertNull($this->object->getIdAttribute($xpath));
    }
}
