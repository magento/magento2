<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Tests\Unit\ReportXml\DB\Assembler;

/**
 * A unit test for testing of the 'from' assembler.
 */
class FromAssemblerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Analytics\ReportXml\DB\Assembler\FromAssembler
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
     * @var \Magento\Analytics\ReportXml\DB\ColumnsResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $columnsResolverMock;

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
            ->method('getColumns')
            ->willReturn([]);

        $this->columnsResolverMock = $this->getMockBuilder(
            \Magento\Analytics\ReportXml\DB\ColumnsResolver::class
        )
        ->disableOriginalConstructor()
        ->getMock();

        $this->objectManagerHelper =
            new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->subject = $this->objectManagerHelper->getObject(
            \Magento\Analytics\ReportXml\DB\Assembler\FromAssembler::class,
            [
                'nameResolver' => $this->nameResolverMock,
                'columnsResolver' => $this->columnsResolverMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testAssemble()
    {
        $queryConfigMock = [
            'source' => [
                'name' => 'sales_order',
                'alias' => 'sales',
                'attribute' => [
                    [
                        'name' => 'entity_id'
                    ]
                ]
            ]
        ];

        $this->nameResolverMock->expects($this->any())
            ->method('getAlias')
            ->with($queryConfigMock['source'])
            ->willReturn($queryConfigMock['source']['alias']);

        $this->nameResolverMock->expects($this->any())
            ->method('getName')
            ->with($queryConfigMock['source'])
            ->willReturn($queryConfigMock['source']['name']);

        $this->selectBuilderMock->expects($this->once())
            ->method('setFrom')
            ->with([$queryConfigMock['source']['alias'] => $queryConfigMock['source']['name']]);

        $this->columnsResolverMock->expects($this->once())
            ->method('getColumns')
            ->with($this->selectBuilderMock, $queryConfigMock['source'])
            ->willReturn(['entity_id' => 'sales.entity_id']);

        $this->selectBuilderMock->expects($this->once())
            ->method('setColumns')
            ->with(['entity_id' => 'sales.entity_id']);

        $this->assertEquals(
            $this->selectBuilderMock,
            $this->subject->assemble($this->selectBuilderMock, $queryConfigMock)
        );
    }
}
