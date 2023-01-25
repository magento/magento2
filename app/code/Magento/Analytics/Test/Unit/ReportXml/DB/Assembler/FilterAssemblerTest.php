<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\ReportXml\DB\Assembler;

use Magento\Analytics\ReportXml\DB\Assembler\FilterAssembler;
use Magento\Analytics\ReportXml\DB\ConditionResolver;
use Magento\Analytics\ReportXml\DB\NameResolver;
use Magento\Analytics\ReportXml\DB\SelectBuilder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * A unit test for testing of the 'filter' assembler.
 */
class FilterAssemblerTest extends TestCase
{
    /**
     * @var FilterAssembler
     */
    private $subject;

    /**
     * @var NameResolver|MockObject
     */
    private $nameResolverMock;

    /**
     * @var SelectBuilder|MockObject
     */
    private $selectBuilderMock;

    /**
     * @var ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var ConditionResolver|MockObject
     */
    private $conditionResolverMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->nameResolverMock = $this->createMock(NameResolver::class);

        $this->selectBuilderMock = $this->createMock(SelectBuilder::class);
        $this->selectBuilderMock
            ->method('getFilters')
            ->willReturn([]);

        $this->conditionResolverMock = $this->createMock(ConditionResolver::class);

        $this->objectManagerHelper =
            new ObjectManager($this);

        $this->subject = $this->objectManagerHelper->getObject(
            FilterAssembler::class,
            [
                'conditionResolver' => $this->conditionResolverMock,
                'nameResolver' => $this->nameResolverMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testAssembleEmpty()
    {
        $queryConfigMock = [
            'source' => [
                'name' => 'sales_order',
                'alias' => 'sales'
            ]
        ];

        $this->selectBuilderMock->expects($this->never())
            ->method('setFilters');

        $this->assertEquals(
            $this->selectBuilderMock,
            $this->subject->assemble($this->selectBuilderMock, $queryConfigMock)
        );
    }

    /**
     * @return void
     */
    public function testAssembleNotEmpty()
    {
        $queryConfigMock = [
            'source' => [
                'name' => 'sales_order',
                'alias' => 'sales',
                'filter' => [
                    [
                        'glue' => 'and',
                        'condition' => [
                            [
                                'attribute' => 'entity_id',
                                'operator' => 'null'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->nameResolverMock
            ->method('getAlias')
            ->with($queryConfigMock['source'])
            ->willReturn($queryConfigMock['source']['alias']);

        $this->conditionResolverMock->expects($this->once())
            ->method('getFilter')
            ->with(
                $this->selectBuilderMock,
                $queryConfigMock['source']['filter'],
                $queryConfigMock['source']['alias']
            )
            ->willReturn('(sales.entity_id IS NULL)');

        $this->selectBuilderMock->expects($this->once())
            ->method('setFilters')
            ->with(['(sales.entity_id IS NULL)']);

        $this->assertEquals(
            $this->selectBuilderMock,
            $this->subject->assemble($this->selectBuilderMock, $queryConfigMock)
        );
    }
}
