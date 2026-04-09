<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\CustomerImportExport\Model\ResourceModel\Import\CustomerComposite\Data
 */
namespace Magento\CustomerImportExport\Test\Unit\Model\ResourceModel\Import\CustomerComposite;

use Magento\CustomerImportExport\Model\Import\Address;
use Magento\CustomerImportExport\Model\Import\CustomerComposite;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Statement\Pdo\Mysql;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @phpstan-ignore-next-line
 */
class DataTest extends TestCase
{
    use MockCreationTrait;
    /**
     * Array of customer attributes
     *
     * @var array
     */
    protected $_customerAttributes = ['customer_attribute1', 'customer_attribute2'];

    /**
     * Generate dependencies for model
     *
     * @param string $entityType
     * @param array $bunchData
     * @return array
     */
    protected function _getDependencies($entityType, $bunchData)
    {
        /** @var $statementMock \Magento\Framework\DB\Statement\Pdo\Mysql */
        $statementMock = $this->createPartialMock(
            Mysql::class,
            ['setFetchMode', 'getIterator']
        );
        $statementMock->expects(
            $this->any()
        )->method(
            'getIterator'
        )->willReturn(
            new \ArrayIterator($bunchData)
        );

        /** @var $selectMock \Magento\Framework\DB\Select */
        $selectMock = $this->createPartialMock(Select::class, ['from', 'order']);
        $selectMock->expects($this->any())->method('from')->willReturnSelf();
        $selectMock->expects($this->any())->method('order')->willReturnSelf();

        /** @var AdapterInterface $connectionMock */
        $connectionMock = $this->createPartialMockWithReflection(
            \stdClass::class,
            ['select', 'query', 'fetchAll', 'fetchRow', 'getTransactionLevel']
        );
        $connectionMock->method('select')->willReturn($selectMock);
        $connectionMock->method('query')->willReturn($statementMock);
        $connectionMock->method('fetchAll')->willReturn($bunchData);
        $connectionMock->method('fetchRow')->willReturn($bunchData[0] ?? []);
        $connectionMock->method('getTransactionLevel')->willReturn(0);

        /** @var $resourceModelMock \Magento\Framework\App\ResourceConnection */
        $resourceModelMock = $this->createMock(ResourceConnection::class);
        $resourceModelMock->expects($this->any())->method('getConnection')->willReturn($connectionMock);

        $data = ['resource' => $resourceModelMock, 'entity_type' => $entityType];

        if ($entityType == CustomerComposite::COMPONENT_ENTITY_ADDRESS) {
            $data['customer_attributes'] = $this->_customerAttributes;
        }

        return $data;
    }

    /**
     * @covers \Magento\CustomerImportExport\Model\ResourceModel\Import\CustomerComposite\Data::getNextBunch
     * @covers \Magento\CustomerImportExport\Model\ResourceModel\Import\CustomerComposite\Data::_prepareRow
     * @covers \Magento\CustomerImportExport\Model\ResourceModel\Import\CustomerComposite\Data::_prepareAddressRowData
     *
     * @param string $entityType
     * @param string $bunchData
     * @param array $expectedData
     */
    #[DataProvider('getNextBunchDataProvider')]
    public function testGetNextBunch($entityType, $bunchData, $expectedData)
    {
        $dependencies = $this->_getDependencies($entityType, [[$bunchData]]);

        $resource = $dependencies['resource'];
        $helper = new ObjectManager($this);
        $jsonDecoderMock = $this->createMock(DecoderInterface::class);
        $jsonDecoderMock->expects($this->once())
            ->method('decode')
            ->willReturn(json_decode($bunchData, true));
        $jsonHelper = $helper->getObject(
            Data::class,
            [
                'jsonDecoder' => $jsonDecoderMock,
            ]
        );
        unset($dependencies['resource'], $dependencies['json_helper']);

        $contextMock = $this->createMock(Context::class);
        $contextMock->expects($this->once())->method('getResources')->willReturn($resource);

        $objectManager = new ObjectManager($this);
        $object = $objectManager->getObject(
            \Magento\CustomerImportExport\Model\ResourceModel\Import\CustomerComposite\Data::class,
            [
                'context' => $contextMock,
                'jsonHelper' => $jsonHelper,
                'arguments' => $dependencies,
            ]
        );
        $this->assertEquals($expectedData, $object->getNextUniqueBunch());
    }

    /**
     * Data provider of row data and expected result of getNextBunch() method
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function getNextBunchDataProvider()
    {
        return [
            'address entity' => [
                'entityType' => CustomerComposite::COMPONENT_ENTITY_ADDRESS,
                'bunchData' => json_encode(
                    [
                        [
                            '_scope' => CustomerComposite::SCOPE_DEFAULT,
                            Address::COLUMN_WEBSITE => 'website1',
                            Address::COLUMN_EMAIL => 'email1',
                            Address::COLUMN_ADDRESS_ID => null,
                            CustomerComposite::COLUMN_DEFAULT_BILLING => 'value',
                            CustomerComposite::COLUMN_DEFAULT_SHIPPING => 'value',
                            'customer_attribute1' => 'value',
                            'customer_attribute2' => 'value',
                            CustomerComposite::COLUMN_ADDRESS_PREFIX . 'attribute1' => 'value',
                            CustomerComposite::COLUMN_ADDRESS_PREFIX . 'attribute2' => 'value',
                        ],
                    ]
                ),
                'expectedData' => [
                    0 => [
                        Address::COLUMN_WEBSITE => 'website1',
                        Address::COLUMN_EMAIL => 'email1',
                        Address::COLUMN_ADDRESS_ID => null,
                        CustomerComposite::COLUMN_DEFAULT_BILLING => 'value',
                        CustomerComposite::COLUMN_DEFAULT_SHIPPING => 'value',
                        'attribute1' => 'value',
                        'attribute2' => 'value',
                    ],
                ],
            ],
            'customer entity default scope' => [
                'entityType' => CustomerComposite::COMPONENT_ENTITY_CUSTOMER,
                'bunchData' => json_encode(
                    [
                        [
                            '_scope' => CustomerComposite::SCOPE_DEFAULT,
                            Address::COLUMN_WEBSITE => 'website1',
                            Address::COLUMN_EMAIL => 'email1',
                            Address::COLUMN_ADDRESS_ID => null,
                            CustomerComposite::COLUMN_DEFAULT_BILLING => 'value',
                            CustomerComposite::COLUMN_DEFAULT_SHIPPING => 'value',
                            'customer_attribute1' => 'value',
                            'customer_attribute2' => 'value',
                            CustomerComposite::COLUMN_ADDRESS_PREFIX . 'attribute1' => 'value',
                            CustomerComposite::COLUMN_ADDRESS_PREFIX . 'attribute2' => 'value',
                        ],
                    ]
                ),
                'expectedData' => [
                    0 => [
                        Address::COLUMN_WEBSITE => 'website1',
                        Address::COLUMN_EMAIL => 'email1',
                        Address::COLUMN_ADDRESS_ID => null,
                        CustomerComposite::COLUMN_DEFAULT_BILLING => 'value',
                        CustomerComposite::COLUMN_DEFAULT_SHIPPING => 'value',
                        'customer_attribute1' => 'value',
                        'customer_attribute2' => 'value',
                        CustomerComposite::COLUMN_ADDRESS_PREFIX . 'attribute1' => 'value',
                        CustomerComposite::COLUMN_ADDRESS_PREFIX . 'attribute2' => 'value',
                    ],
                ],
            ],
            'customer entity address scope' => [
                'entityType' => CustomerComposite::COMPONENT_ENTITY_CUSTOMER,
                'bunchData' => json_encode(
                    [
                        [
                            '_scope' => CustomerComposite::SCOPE_ADDRESS,
                            Address::COLUMN_WEBSITE => 'website1',
                            Address::COLUMN_EMAIL => 'email1',
                            Address::COLUMN_ADDRESS_ID => null,
                            CustomerComposite::COLUMN_DEFAULT_BILLING => 'value',
                            CustomerComposite::COLUMN_DEFAULT_SHIPPING => 'value',
                            'customer_attribute1' => 'value',
                            'customer_attribute2' => 'value',
                            CustomerComposite::COLUMN_ADDRESS_PREFIX . 'attribute1' => 'value',
                            CustomerComposite::COLUMN_ADDRESS_PREFIX . 'attribute2' => 'value',
                        ],
                    ]
                ),
                'expectedData' => [],
            ]
        ];
    }
}
