<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Test\Unit;

/**
 * Class GenericMapperTest
 */
class GenericMapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectMock;

    /**
     * @var \Magento\Framework\DB\MapperFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mapperFactoryMock;

    /**
     * @var \Magento\Framework\DB\GenericMapper
     */
    protected $geneticMapper;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->selectMock = $this->createPartialMock(
            \Magento\Framework\DB\Select::class,
            ['orWhere', 'where', 'setPart', 'getPart']
        );
        $this->mapperFactoryMock = $this->createPartialMock(\Magento\Framework\DB\MapperFactory::class, ['create']);

        $this->geneticMapper = $objectManager->getObject(
            \Magento\Framework\DB\GenericMapper::class,
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
            \Magento\Framework\Api\CriteriaInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getMapperInterfaceName']
        );
        $mapperInstanceMock = $this->getMockForAbstractClass(
            \Magento\Framework\DB\MapperInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['map']
        );

        $criteriaMock->expects($this->any())
            ->method('getMapperInterfaceName')
            ->will($this->returnValue('mapper-name'));
        $this->mapperFactoryMock->expects($this->exactly(4))
            ->method('create')
            ->with('mapper-name', ['select' => $this->selectMock])
            ->will($this->returnValue($mapperInstanceMock));
        $mapperInstanceMock->expects($this->exactly(4))
            ->method('map')
            ->will($this->returnValue($this->selectMock));

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
            \Magento\Framework\DB\Adapter\AdapterInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['quoteInto', 'prepareSqlCondition']
        );

        /** @var \Magento\Framework\DB\GenericMapper|\PHPUnit_Framework_MockObject_MockObject $geneticMapper */
        $geneticMapper =
            $this->createPartialMock(\Magento\Framework\DB\GenericMapper::class, ['getConnection', 'getSelect']);

        $geneticMapper->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($connectionMock));
        $geneticMapper->expects($this->exactly(4))
            ->method('getSelect')
            ->will($this->returnValue($this->selectMock));
        $connectionMock->expects($this->exactly(2))
            ->method('quoteInto')
            ->with('test-field=?', 'test-condition')
            ->will($this->returnValue('test-condition'));
        $this->selectMock->expects($this->once())
            ->method('orWhere')
            ->with('test-condition');
        $this->selectMock->expects($this->exactly(3))
            ->method('where')
            ->with('test-condition');
        $connectionMock->expects($this->any())
            ->method('prepareSqlCondition')
            ->with('test-field', 'test-condition')
            ->will($this->returnValue('test-condition'));

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

        /** @var \Magento\Framework\DB\GenericMapper|\PHPUnit_Framework_MockObject_MockObject $geneticMapper */
        $geneticMapper = $this->createPartialMock(\Magento\Framework\DB\GenericMapper::class, ['getSelect']);

        $geneticMapper->expects($this->any())
            ->method('getSelect')
            ->will($this->returnValue($this->selectMock));
        $this->selectMock->expects($this->once())
            ->method('getPart')
            ->with(\Magento\Framework\DB\Select::COLUMNS)
            ->willReturn([]);
        $this->selectMock->expects($this->once())
            ->method('setPart')
            ->with(\Magento\Framework\DB\Select::COLUMNS, $this->equalTo($fields));

        $geneticMapper->mapFields($fields);
    }
}
