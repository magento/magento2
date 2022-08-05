<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Test\Unit;

use Magento\Framework\Api\CriteriaInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\ObjectFactory;
use Magento\Framework\DB\AbstractMapper;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\MapperFactory;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class AbstractMapperTest extends TestCase
{
    /**
     * @var AbstractDb|MockObject
     */
    protected $resourceMock;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $connectionMock;

    /**
     * @var Select|MockObject
     */
    protected $selectMock;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $loggerMock;

    /**
     * @var FetchStrategyInterface|MockObject
     */
    protected $fetchStrategyMock;

    /**
     * @var ObjectFactory|MockObject
     */
    protected $objectFactoryMock;

    /**
     * @var MapperFactory|MockObject
     */
    protected $mapperFactoryMock;

    /**
     * @var AbstractMapper|MockObject
     */
    protected $mapper;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->resourceMock = $this->getMockForAbstractClass(
            AbstractDb::class,
            [],
            '',
            false,
            true,
            true,
            []
        );
        $this->connectionMock = $this->getMockForAbstractClass(
            AdapterInterface::class,
            [],
            '',
            false,
            true,
            true,
            []
        );
        $this->selectMock = $this->createMock(Select::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->fetchStrategyMock = $this->getMockForAbstractClass(
            FetchStrategyInterface::class,
            [],
            '',
            false,
            true,
            true,
            []
        );
        $this->objectFactoryMock = $this->createMock(ObjectFactory::class);
        $this->mapperFactoryMock = $this->createMock(MapperFactory::class);
    }

    /**
     * Run test map method
     *
     * @param array $mapperMethods
     * @param array $criteriaParts
     * @return void
     *
     * @dataProvider dataProviderMap
     */
    public function testMap(array $mapperMethods, array $criteriaParts)
    {
        /** @var AbstractMapper|MockObject $mapper */
        $mapper = $this->getMockForAbstractClass(
            AbstractMapper::class,
            [
                'logger' => $this->loggerMock,
                'fetchStrategy' => $this->fetchStrategyMock,
                'objectFactory' => $this->objectFactoryMock,
                'mapperFactory' => $this->mapperFactoryMock,
                'select' => $this->selectMock
            ],
            '',
            true,
            true,
            true,
            $mapperMethods
        );
        $criteriaMock = $this->getMockForAbstractClass(
            CriteriaInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['toArray']
        );
        $criteriaMock->expects($this->once())
            ->method('toArray')
            ->willReturn($criteriaParts);
        foreach ($mapperMethods as $value => $method) {
            $mapper->expects($this->once())
                ->method($method)
                ->with($value);
        }

        $this->assertEquals($this->selectMock, $mapper->map($criteriaMock));
    }

    public function testMapException()
    {
        $mapperMethods = [
            'my-test-value1' => 'mapMyMapperMethodOne'
        ];

        $criteriaParts = [
            'my_mapper_method_one' => 'my-test-value1'
        ];
        /** @var AbstractMapper|MockObject $mapper */
        $mapper = $this->getMockForAbstractClass(
            AbstractMapper::class,
            [
                'logger' => $this->loggerMock,
                'fetchStrategy' => $this->fetchStrategyMock,
                'objectFactory' => $this->objectFactoryMock,
                'mapperFactory' => $this->mapperFactoryMock,
                'select' => $this->selectMock
            ],
            '',
            true,
            true,
            true,
            $mapperMethods
        );
        $criteriaMock = $this->getMockForAbstractClass(
            CriteriaInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['toArray']
        );
        $criteriaMock->expects($this->once())
            ->method('toArray')
            ->willReturn($criteriaParts);
        $this->expectException(\InvalidArgumentException::class);
        $mapper->map($criteriaMock);
    }

    /**
     * Run test addExpressionFieldToSelect method
     *
     * @return void
     */
    public function testAddExpressionFieldToSelect()
    {
        $fields = [
            'key-attribute' => 'value-attribute',
        ];
        /** @var AbstractMapper|MockObject $mapper */
        $mapper = $this->getMockForAbstractClass(
            AbstractMapper::class,
            [
                'logger' => $this->loggerMock,
                'fetchStrategy' => $this->fetchStrategyMock,
                'objectFactory' => $this->objectFactoryMock,
                'mapperFactory' => $this->mapperFactoryMock,
                'select' => $this->selectMock
            ],
            '',
            true,
            true,
            true,
            []
        );

        $this->selectMock->expects($this->once())
            ->method('columns')
            ->with(['my-alias' => "('sub_total', 'SUM(value-attribute)', 'revenue')"]);

        $mapper->addExpressionFieldToSelect('my-alias', "('sub_total', 'SUM({{key-attribute}})', 'revenue')", $fields);
    }

    /**
     * Run test addExpressionFieldToSelect method
     *
     * @param mixed $field
     * @param mixed $condition
     * @return void
     *
     * @dataProvider dataProviderAddFieldToFilter
     */
    public function testAddFieldToFilter($field, $condition)
    {
        $resultCondition = 'sql-condition-value';

        /** @var AbstractMapper|MockObject $mapper */
        $mapper = $this->getMockForAbstractClass(
            AbstractMapper::class,
            [
                'logger' => $this->loggerMock,
                'fetchStrategy' => $this->fetchStrategyMock,
                'objectFactory' => $this->objectFactoryMock,
                'mapperFactory' => $this->mapperFactoryMock,
                'select' => $this->selectMock
            ],
            '',
            true,
            true,
            true,
            ['getConnection']
        );
        $connectionMock = $this->getMockForAbstractClass(
            AdapterInterface::class,
            [],
            '',
            true,
            true,
            true,
            ['quoteIdentifier', 'prepareSqlCondition']
        );

        $mapper->expects($this->any())
            ->method('getConnection')
            ->willReturn($connectionMock);
        $connectionMock->expects($this->any())
            ->method('quoteIdentifier')
            ->with('my-field')
            ->willReturn('quote-field');
        $connectionMock->expects($this->any())
            ->method('prepareSqlCondition')
            ->with('quote-field', $condition)
            ->willReturn($resultCondition);

        if (is_array($field)) {
            $resultCondition = '(' . implode(
                ') ' . Select::SQL_OR . ' (',
                array_fill(0, count($field), $resultCondition)
            ) . ')';
        }

        $this->selectMock->expects($this->once())
            ->method('where')
            ->with($resultCondition, null, Select::TYPE_CONDITION);

        $mapper->addFieldToFilter($field, $condition);
    }

    /**
     * Data provider for map method
     *
     * @return array
     */
    public function dataProviderMap()
    {
        return [
            [
                'mapperMethods' => [
                    'my-test-value1' => 'mapMyMapperMethodOne',
                    'my-test-value2' => 'mapMyMapperMethodTwo',
                ],
                'criteriaParts' => [
                    'my_mapper_method_one' => ['my-test-value1'],
                    'my_mapper_method_two' => ['my-test-value2'],
                ],
            ]
        ];
    }

    /**
     * Data provider for addFieldToFilter method
     *
     * @return array
     */
    public function dataProviderAddFieldToFilter()
    {
        return [
            [
                'field' => 'my-field',
                'condition' => ['condition'],
            ],
            [
                'field' => ['my-field', 'my-field'],
                'condition' => null
            ],
        ];
    }
}
