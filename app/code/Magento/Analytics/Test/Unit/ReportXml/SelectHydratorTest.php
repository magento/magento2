<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\ReportXml;

use Magento\Analytics\ReportXml\SelectHydrator;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SelectHydratorTest extends TestCase
{
    /**
     * @var SelectHydrator
     */
    private $selectHydrator;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var Select|MockObject
     */
    private $selectMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);

        $this->connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);

        $this->selectMock = $this->createMock(Select::class);

        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->selectHydrator = $this->objectManagerHelper->getObject(
            SelectHydrator::class,
            [
                'resourceConnection' => $this->resourceConnectionMock,
                'objectManager' => $this->objectManagerMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testExtract(): void
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
        $this->selectMock
            ->method('getPart')
            ->willReturn("Part");
        $this->assertEquals($this->selectHydrator->extract($this->selectMock), $result);
    }

    /**
     * @param array $selectParts
     * @param array $parts
     * @param array $partValues
     *
     * @return void
     * @dataProvider recreateWithoutExpressionDataProvider
     */
    public function testRecreateWithoutExpression(array $selectParts, array $parts, array $partValues): void
    {
        $this->resourceConnectionMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($this->selectMock);
        $withArgs = [];

        foreach ($parts as $key => $part) {
            $withArgs[] = [$part, $partValues[$key]];
        }
        $this->selectMock
            ->method('setPart')
            ->withConsecutive(...$withArgs);

        $this->assertSame($this->selectMock, $this->selectHydrator->recreate($selectParts));
    }

    /**
     * @return array
     */
    public function recreateWithoutExpressionDataProvider(): array
    {
        return [
            'Select without expressions' => [
                [
                    Select::COLUMNS => [
                        [
                            'table_name',
                            'field_name',
                            'alias'
                        ],
                        [
                            'table_name',
                            'field_name_2',
                            'alias_2'
                        ],
                    ]
                ],
                [Select::COLUMNS],
                [[
                    [
                        'table_name',
                        'field_name',
                        'alias'
                    ],
                    [
                        'table_name',
                        'field_name_2',
                        'alias_2'
                    ]
                ]]
            ]
        ];
    }

    /**
     * @param array $selectParts
     * @param array $expectedParts
     * @param MockObject[] $expressionMocks
     *
     * @return void
     * @dataProvider recreateWithExpressionDataProvider
     */
    public function testRecreateWithExpression(
        array $selectParts,
        array $expectedParts,
        array $expressionMocks
    ): void {
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
        $withArgs = [];

        foreach (array_keys($selectParts) as $partName) {
            $withArgs[] = [$partName, $expectedParts[$partName]];
        }
        $this->selectMock
            ->method('setPart')
            ->withConsecutive(...$withArgs);

        $this->assertSame($this->selectMock, $this->selectHydrator->recreate($selectParts));
    }

    /**
     * @return array
     */
    public function recreateWithExpressionDataProvider(): array
    {
        $expressionMock = $this->createMock(\JsonSerializable::class);

        return [
            'Select without expressions' => [
                'Parts' => [
                    Select::COLUMNS => [
                        [
                            'table_name',
                            'field_name',
                            'alias'
                        ],
                        [
                            'table_name',
                            [
                                'class' => 'Some_class',
                                'arguments' => [
                                    'expression' => ['some(expression)']
                                ]
                            ],
                            'alias_2'
                        ]
                    ]
                ],
                'expectedParts' => [
                    Select::COLUMNS => [
                        [
                            'table_name',
                            'field_name',
                            'alias'
                        ],
                        [
                            'table_name',
                            $expressionMock,
                            'alias_2'
                        ]
                    ]
                ],
                'expectedExpressions' => [
                    $expressionMock
                ]
            ]
        ];
    }
}
