<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\CustomerImportExport\Model\ResourceModel\Import\CustomerComposite\Data
 */
namespace Magento\CustomerImportExport\Test\Unit\Model\ResourceModel\Import\CustomerComposite;

use Magento\CustomerImportExport\Model\Import\Address;
use Magento\CustomerImportExport\Model\Import\CustomerComposite;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Statement\Pdo\Mysql;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataTest extends TestCase
{
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
        )->will(
            $this->returnValue(new \ArrayIterator($bunchData))
        );

        /** @var $selectMock \Magento\Framework\DB\Select */
        $selectMock = $this->createPartialMock(Select::class, ['from', 'order']);
        $selectMock->expects($this->any())->method('from')->will($this->returnSelf());
        $selectMock->expects($this->any())->method('order')->will($this->returnSelf());

        /** @var $connectionMock \Magento\Framework\DB\Adapter\AdapterInterface */
        $connectionMock = $this->createPartialMock(
            \Magento\Framework\DB\Adapter\Pdo\Mysql::class,
            ['select', 'from', 'order', 'query']
        );
        $connectionMock->expects($this->any())->method('select')->will($this->returnValue($selectMock));
        $connectionMock->expects($this->any())->method('query')->will($this->returnValue($statementMock));

        /** @var $resourceModelMock \Magento\Framework\App\ResourceConnection */
        $resourceModelMock = $this->createMock(ResourceConnection::class);
        $resourceModelMock->expects($this->any())->method('getConnection')->will($this->returnValue($connectionMock));

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
     * @dataProvider getNextBunchDataProvider
     * @param string $entityType
     * @param string $bunchData
     * @param array $expectedData
     */
    public function testGetNextBunch($entityType, $bunchData, $expectedData)
    {
        $dependencies = $this->_getDependencies($entityType, [[$bunchData]]);

        $resource = $dependencies['resource'];
        $helper = new ObjectManager($this);
        $jsonDecoderMock = $this->getMockBuilder(DecoderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
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
        $this->assertEquals($expectedData, $object->getNextBunch());
    }

    /**
     * Data provider of row data and expected result of getNextBunch() method
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getNextBunchDataProvider()
    {
        return [
            'address entity' => [
                '$entityType' => CustomerComposite::COMPONENT_ENTITY_ADDRESS,
                '$bunchData' => json_encode(
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
                '$expectedData' => [
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
                '$entityType' => CustomerComposite::COMPONENT_ENTITY_CUSTOMER,
                '$bunchData' => json_encode(
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
                '$expectedData' => [
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
                '$entityType' => CustomerComposite::COMPONENT_ENTITY_CUSTOMER,
                '$bunchData' => json_encode(
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
                '$expectedData' => [],
            ]
        ];
    }
}
