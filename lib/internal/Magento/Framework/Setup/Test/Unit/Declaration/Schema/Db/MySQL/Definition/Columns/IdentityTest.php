<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit\Declaration\Schema\Db\MySQL\Definition\Columns;

use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Columns\Comment;
use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Columns\Identity;
use Magento\Framework\Setup\Declaration\Schema\Dto\Column;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Identity DTO class.
 *
 */
class IdentityTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Comment
     */
    private $identity;

    /**
     * @var Column|MockObject
     */
    private $columnMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->columnMock = $this->getMockBuilder(Column::class)
            ->disableOriginalConstructor()
            ->setMethods(['isIdentity'])
            ->getMock();
        $this->identity = $this->objectManager->getObject(
            Identity::class
        );
    }

    /**
     * Test conversion to definition.
     */
    public function testToDefinition()
    {
        $this->columnMock->expects($this->any())
            ->method('isIdentity')
            ->willReturn(true);
        $this->assertEquals(
            'AUTO_INCREMENT',
            $this->identity->toDefinition($this->columnMock)
        );
    }

    /**
     * Test conversion to definition.
     */
    public function testToDefinitionFalse()
    {
        $this->columnMock->expects($this->any())
            ->method('isIdentity')
            ->willReturn(false);
        $this->assertEquals(
            '',
            $this->identity->toDefinition($this->columnMock)
        );
    }

    /**
     * Test from definition.
     */
    public function testFromDefinition()
    {
        $data = [
            'extra' => 'NOT NULL AUTO_INCREMENT'
        ];
        $expectedData = $data;
        $expectedData['identity'] = true;
        $this->assertEquals(
            $expectedData,
            $this->identity->fromDefinition($data)
        );
    }
}
