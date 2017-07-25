<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\ReportXml;

use Magento\Analytics\ReportXml\SelectHydrator;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\JsonSerializableExpression;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class SelectHydratorTest
 */
class SelectHydratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SelectHydrator
     */
    private $selectHydrator;

    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionMock;

    /**
     * @var Select|\PHPUnit_Framework_MockObject_MockObject
     */
    private $selectMock;

    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->selectHydrator = $this->objectManagerHelper->getObject(
            SelectHydrator::class,
            [
                'resourceConnection' => $this->resourceConnectionMock,
                'objectManager' => $this->objectManagerMock,
            ]
        );
    }

    public function testExtract()
    {
        $selectParts =
            [
                Select::DISTINCT,
                Select::COLUMNS,
                Select::UNION,
                Select::FROM,
                Select::WHERE,
                Select::GROUP,
                Select::HAVING,
                Select::ORDER,
                Select::LIMIT_COUNT,
                Select::LIMIT_OFFSET,
                Select::FOR_UPDATE
            ];

        $result = [];
        foreach ($selectParts as $part) {
            $result[$part] = "Part";
        }
        $this->selectMock->expects($this->any())
            ->method('getPart')
            ->willReturn("Part");
        $this->assertEquals($this->selectHydrator->extract($this->selectMock), $result);
    }

    /**
     * @dataProvider recreateWithoutExpressionDataProvider
     * @param array $selectParts
     * @param array $parts
     * @param array $partValues
     */
    public function testRecreateWithoutExpression($selectParts, $parts, $partValues)
    {
        $this->resourceConnectionMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($this->selectMock);
        foreach ($parts as $key => $part) {
            $this->selectMock->expects($this->at($key))
                ->method('setPart')
                ->with($part, $partValues[$key]);
        }

        $this->assertSame($this->selectMock, $this->selectHydrator->recreate($selectParts));
    }

    /**
     * @return array
     */
    public function recreateWithoutExpressionDataProvider()
    {
        return [
            'Select without expressions' => [
                [
                    Select::COLUMNS => [
                        [
                            'table_name',
                            'field_name',
                            'alias',
                        ],
                        [
                            'table_name',
                            'field_name_2',
                            'alias_2',
                        ],
                    ]
                ],
                [Select::COLUMNS],
                [[
                    [
                        'table_name',
                        'field_name',
                        'alias',
                    ],
                    [
                        'table_name',
                        'field_name_2',
                        'alias_2',
                    ],
                ]],
            ],
        ];
    }

    /**
     * @dataProvider recreateWithExpressionDataProvider
     * @param array $selectParts
     * @param array $expectedParts
     * @param \PHPUnit_Framework_MockObject_MockObject[] $expressionMocks
     */
    public function testRecreateWithExpression(
        array $selectParts,
        array $expectedParts,
        array $expressionMocks
    ) {
        $this->objectManagerMock
            ->expects($this->exactly(count($expressionMocks)))
            ->method('create')
            ->with($this->isType('string'), $this->isType('array'))
            ->willReturnOnConsecutiveCalls(...$expressionMocks);
        $this->resourceConnectionMock
            ->expects($this->once())
            ->method('getConnection')
            ->with()
            ->willReturn($this->connectionMock);
        $this->connectionMock
            ->expects($this->once())
            ->method('select')
            ->with()
            ->willReturn($this->selectMock);
        foreach (array_keys($selectParts) as $key => $partName) {
            $this->selectMock
                ->expects($this->at($key))
                ->method('setPart')
                ->with($partName, $expectedParts[$partName]);
        }

        $this->assertSame($this->selectMock, $this->selectHydrator->recreate($selectParts));
    }

    /**
     * @return array
     */
    public function recreateWithExpressionDataProvider()
    {
        $expressionMock = $this->getMockBuilder(JsonSerializableExpression::class)
            ->disableOriginalConstructor()
            ->getMock();

        return [
            'Select without expressions' => [
                'Parts' => [
                    Select::COLUMNS => [
                        [
                            'table_name',
                            'field_name',
                            'alias',
                        ],
                        [
                            'table_name',
                            [
                                'class' => 'Some_class',
                                'arguments' => [
                                    'expression' => ['some(expression)']
                                ]
                            ],
                            'alias_2',
                        ],
                    ]
                ],
                'expectedParts' => [
                    Select::COLUMNS => [
                        [
                            'table_name',
                            'field_name',
                            'alias',
                        ],
                        [
                            'table_name',
                            $expressionMock,
                            'alias_2',
                        ],
                    ]
                ],
                'expectedExpressions' => [
                    $expressionMock
                ]
            ],
        ];
    }
}
