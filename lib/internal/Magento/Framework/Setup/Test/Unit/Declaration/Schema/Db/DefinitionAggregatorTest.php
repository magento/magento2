<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Test\Unit\Declaration\Schema\Db;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Setup\Declaration\Schema\Db\DbDefinitionProcessorInterface;
use Magento\Framework\Setup\Declaration\Schema\Db\DefinitionAggregator;
use Magento\Framework\Setup\Declaration\Schema\Dto\ElementInterface;

/**
 * Test for Definition Aggregator.
 *
 */
class DefinitionAggregatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DefinitionAggregator
     */
    private $definitonAggregator;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var DbDefinitionProcessorInterface[]|\PHPUnit\Framework\MockObject\MockObject[]
     */
    private $definitonProcessors;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $intDefProcessor = $this->getMockBuilder(DbDefinitionProcessorInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        $varcharDefProcessor = $this->getMockBuilder(DbDefinitionProcessorInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();

        $this->definitonProcessors = [
            'int' => $intDefProcessor,
            'varchar' => $varcharDefProcessor,
        ];
        $this->definitonAggregator = $this->objectManager->getObject(
            DefinitionAggregator::class,
            [
                'definitionProcessors' => $this->definitonProcessors
            ]
        );
    }

    public function testToDefinition()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot process object to definition for type text');
        /** @var ElementInterface|\PHPUnit\Framework\MockObject\MockObject $columnInt */
        $columnInt = $this->getMockBuilder(ElementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        /** @var ElementInterface|\PHPUnit\Framework\MockObject\MockObject $columnVarchar */
        $columnVarchar = $this->getMockBuilder(ElementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        /** @var ElementInterface|\PHPUnit\Framework\MockObject\MockObject $columnText */
        $columnText = $this->getMockBuilder(ElementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $columnInt->expects($this->any())->method('getType')->willReturn('int');
        $columnVarchar->expects($this->any())->method('getType')->willReturn('varchar');
        $columnText->expects($this->any())->method('getType')->willReturn('text');
        $this->definitonProcessors['int']->expects($this->once())->method('toDefinition');
        $this->definitonProcessors['varchar']->expects($this->once())->method('toDefinition');
        $this->definitonAggregator->toDefinition($columnInt);
        $this->definitonAggregator->toDefinition($columnVarchar);
        $this->definitonAggregator->toDefinition($columnText);
    }

    /**
     * Cannot process definition to array for type text
     */
    public function testFromDefinition()
    {
        $this->expectException(\InvalidArgumentException::class);

        $data = [
            'col_int' => [
                'type' => 'int'
            ],
            'col_varchar' => [
                'type' => 'varchar'
            ],
            'col_text' => [
                'type' => 'text'
            ],
        ];
        $this->definitonProcessors['int']->expects($this->once())->method('fromDefinition');
        $this->definitonProcessors['varchar']->expects($this->once())->method('fromDefinition');
        foreach ($data as $colData) {
            $this->definitonAggregator->fromDefinition($colData);
        }
    }
}
