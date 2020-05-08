<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit\Declaration\Schema\Db\MySQL\Definition\Columns;

use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Columns\Comment;
use Magento\Framework\Setup\Declaration\Schema\Dto\Column;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CommentTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Comment
     */
    private $comment;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->comment = $this->objectManager->getObject(
            Comment::class
        );
    }

    /**
     * Test conversion to definition.
     */
    public function testToDefinition()
    {
        /** @var Column|MockObject $column */
        $column = $this->getMockBuilder(Column::class)
            ->disableOriginalConstructor()
            ->getMock();
        $column->expects($this->any())
            ->method('getComment')
            ->willReturn('comment');
        $this->assertEquals(
            'COMMENT "comment"',
            $this->comment->toDefinition($column)
        );
    }
}
