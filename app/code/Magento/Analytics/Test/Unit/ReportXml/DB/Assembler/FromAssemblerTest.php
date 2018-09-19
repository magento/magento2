<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\ReportXml\DB\Assembler;

use Magento\Framework\App\ResourceConnection;

/**
 * A unit test for testing of the 'from' assembler.
 */
class FromAssemblerTest extends \PHPUnit\Framework\TestCase
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
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnection;

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

        $this->resourceConnection = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper =
            new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->subject = $this->objectManagerHelper->getObject(
            \Magento\Analytics\ReportXml\DB\Assembler\FromAssembler::class,
            [
                'nameResolver' => $this->nameResolverMock,
                'columnsResolver' => $this->columnsResolverMock,
                'resourceConnection' => $this->resourceConnection,
            ]
        );
    }

    /**
     * @dataProvider assembleDataProvider
     * @param array $queryConfig
     * @param string $tableName
     * @return void
     */
    public function testAssemble(array $queryConfig, $tableName)
    {
        $this->nameResolverMock->expects($this->any())
            ->method('getAlias')
            ->with($queryConfig['source'])
            ->willReturn($queryConfig['source']['alias']);

        $this->nameResolverMock->expects($this->once())
            ->method('getName')
            ->with($queryConfig['source'])
            ->willReturn($queryConfig['source']['name']);

        $this->resourceConnection
            ->expects($this->once())
            ->method('getTableName')
            ->with($queryConfig['source']['name'])
            ->willReturn($tableName);

        $this->selectBuilderMock->expects($this->once())
            ->method('setFrom')
            ->with([$queryConfig['source']['alias'] => $tableName]);

        $this->columnsResolverMock->expects($this->once())
            ->method('getColumns')
            ->with($this->selectBuilderMock, $queryConfig['source'])
            ->willReturn(['entity_id' => 'sales.entity_id']);

        $this->selectBuilderMock->expects($this->once())
            ->method('setColumns')
            ->with(['entity_id' => 'sales.entity_id']);

        $this->assertEquals(
            $this->selectBuilderMock,
            $this->subject->assemble($this->selectBuilderMock, $queryConfig)
        );
    }

    /**
     * @return array
     */
    public function assembleDataProvider()
    {
        return [
            'Tables without prefixes' => [
                [
                    'source' => [
                        'name' => 'sales_order',
                        'alias' => 'sales',
                        'attribute' => [
                            [
                                'name' => 'entity_id'
                            ]
                        ],
                    ],
                ],
                'sales_order',
            ],
            'Tables with prefixes' => [
                [
                    'source' => [
                        'name' => 'sales_order',
                        'alias' => 'sales',
                        'attribute' => [
                            [
                                'name' => 'entity_id'
                            ]
                        ],
                    ],
                ],
                'pref_sales_order',
            ]
        ];
    }
}
