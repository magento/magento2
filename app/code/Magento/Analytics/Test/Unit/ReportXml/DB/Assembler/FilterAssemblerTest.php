<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\ReportXml\DB\Assembler;

/**
 * A unit test for testing of the 'filter' assembler.
 */
class FilterAssemblerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Analytics\ReportXml\DB\Assembler\FilterAssembler
     */
    private $subject;

    /**
     * @var \Magento\Analytics\ReportXml\DB\NameResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $nameResolverMock;

    /**
     * @var \Magento\Analytics\ReportXml\DB\SelectBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $selectBuilderMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\Analytics\ReportXml\DB\ConditionResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $conditionResolverMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->nameResolverMock = $this->getMockBuilder(
            \Magento\Analytics\ReportXml\DB\NameResolver::class
        )
        ->disableOriginalConstructor()
        ->getMock();

        $this->selectBuilderMock = $this->getMockBuilder(
            \Magento\Analytics\ReportXml\DB\SelectBuilder::class
        )
        ->disableOriginalConstructor()
        ->getMock();
        $this->selectBuilderMock->expects($this->any())
            ->method('getFilters')
            ->willReturn([]);

        $this->conditionResolverMock = $this->getMockBuilder(
            \Magento\Analytics\ReportXml\DB\ConditionResolver::class
        )
        ->disableOriginalConstructor()
        ->getMock();

        $this->objectManagerHelper =
            new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->subject = $this->objectManagerHelper->getObject(
            \Magento\Analytics\ReportXml\DB\Assembler\FilterAssembler::class,
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

        $this->nameResolverMock->expects($this->any())
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
