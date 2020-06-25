<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Test\Unit;

use Magento\Framework\Api\CriteriaInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\GenericMapper;
use Magento\Framework\DB\MapperFactory;
use Magento\Framework\DB\MapperInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GenericMapperTest extends TestCase
{
    /**
     * @var Select|MockObject
     */
    protected $selectMock;

    /**
     * @var MapperFactory|MockObject
     */
    protected $mapperFactoryMock;

    /**
     * @var GenericMapper
     */
    protected $geneticMapper;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->selectMock = $this->createPartialMock(
            Select::class,
            ['orWhere', 'where', 'setPart', 'getPart']
        );
        $this->mapperFactoryMock = $this->createPartialMock(MapperFactory::class, ['create']);

        $this->geneticMapper = $objectManager->getObject(
            GenericMapper::class,
            [
                'select' => $this->selectMock,
                'mapperFactory' => $this->mapperFactoryMock,
            ]
        );
    }

    /**
     * Run test mapCriteriaList method
     *
     * @return void
     */
    public function testMapCriteriaList()
    {
        $criteriaMock = $this->getMockForAbstractClass(
            CriteriaInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getMapperInterfaceName']
        );
        $mapperInstanceMock = $this->getMockForAbstractClass(
            MapperInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['map']
        );

        $criteriaMock->expects($this->any())
            ->method('getMapperInterfaceName')
            ->willReturn('mapper-name');
        $this->mapperFactoryMock->expects($this->exactly(4))
            ->method('create')
            ->with('mapper-name', ['select' => $this->selectMock])
            ->willReturn($mapperInstanceMock);
        $mapperInstanceMock->expects($this->exactly(4))
            ->method('map')
            ->willReturn($this->selectMock);

        $this->geneticMapper->mapCriteriaList(array_fill(0, 4, $criteriaMock));
    }

    /**
     * Run test mapFilters method
     *
     * @return void
     */
    public function testMapFilters()
    {
        $filters = [
            [
                'type' => 'or',
                'field' => 'test-field',
                'condition' => 'test-condition',
            ],
            [
                'type' => 'string',
                'field' => 'test-field',
                'condition' => 'test-condition'
            ],
            [
                'type' => 'public',
                'field' => 'test-field',
                'condition' => 'test-condition'
            ],
            [
                'type' => 'default',
                'field' => 'test-field',
                'condition' => 'test-condition'
            ],
        ];

        $connectionMock = $this->getMockForAbstractClass(
            AdapterInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['quoteInto', 'prepareSqlCondition']
        );

        /** @var GenericMapper|MockObject $geneticMapper */
        $geneticMapper =
            $this->createPartialMock(GenericMapper::class, ['getConnection', 'getSelect']);

        $geneticMapper->expects($this->any())
            ->method('getConnection')
            ->willReturn($connectionMock);
        $geneticMapper->expects($this->exactly(4))
            ->method('getSelect')
            ->willReturn($this->selectMock);
        $connectionMock->expects($this->exactly(2))
            ->method('quoteInto')
            ->with('test-field=?', 'test-condition')
            ->willReturn('test-condition');
        $this->selectMock->expects($this->once())
            ->method('orWhere')
            ->with('test-condition');
        $this->selectMock->expects($this->exactly(3))
            ->method('where')
            ->with('test-condition');
        $connectionMock->expects($this->any())
            ->method('prepareSqlCondition')
            ->with('test-field', 'test-condition')
            ->willReturn('test-condition');

        $geneticMapper->mapFilters($filters);
    }

    /**
     * Run test mapFields method
     *
     * @return void
     */
    public function testMapFields()
    {
        $fields = [
            [
                'test-correlation-name',
                'test-field',
                'test-alias',
            ],
            [
                'test-correlation-name',
                'test-field',
                null
            ],
            [
                'test-correlation-name',
                'test-field',
                'test-alias-unique'
            ],
        ];

        /** @var GenericMapper|MockObject $geneticMapper */
        $geneticMapper = $this->createPartialMock(GenericMapper::class, ['getSelect']);

        $geneticMapper->expects($this->any())
            ->method('getSelect')
            ->willReturn($this->selectMock);
        $this->selectMock->expects($this->once())
            ->method('getPart')
            ->with(Select::COLUMNS)
            ->willReturn([]);
        $this->selectMock->expects($this->once())
            ->method('setPart')
            ->with(Select::COLUMNS, $fields);

        $geneticMapper->mapFields($fields);
    }
}
