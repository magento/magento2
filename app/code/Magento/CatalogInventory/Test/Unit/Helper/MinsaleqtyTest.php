<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class MinsaleqtyTest
 */
class MinsaleqtyTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\CatalogInventory\Helper\Minsaleqty */
    protected $minsaleqty;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeConfigMock;

    /** @var \Magento\Framework\Math\Random|\PHPUnit_Framework_MockObject_MockObject */
    protected $randomMock;

    /** @var \Magento\Framework\Serialize\Serializer\Json|\PHPUnit_Framework_MockObject_MockObject */
    private $serializerMock;

    protected function setUp()
    {
        $this->scopeConfigMock = $this->getMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->randomMock = $this->getMock(\Magento\Framework\Math\Random::class);
        $this->randomMock->expects($this->any())
            ->method('getUniqueHash')
            ->with($this->equalTo('_'))
            ->will($this->returnValue('unique_hash'));

        $groupManagement = $this->getMockBuilder(\Magento\Customer\Api\GroupManagementInterface::class)
            ->setMethods(['getAllCustomersGroup'])
            ->getMockForAbstractClass();

        $allGroup = $this->getMockBuilder(\Magento\Customer\Api\Data\GroupInterface::class)
            ->setMethods(['getId'])
            ->getMockForAbstractClass();

        $allGroup->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(32000));

        $groupManagement->expects($this->any())
            ->method('getAllCustomersGroup')
            ->will($this->returnValue($allGroup));

        $this->serializerMock = $this->getMock(\Magento\Framework\Serialize\Serializer\Json::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->minsaleqty = $this->objectManagerHelper->getObject(
            \Magento\CatalogInventory\Helper\Minsaleqty::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'mathRandom' => $this->randomMock,
                'groupManagement' => $groupManagement,
                'serializer' => $this->serializerMock
            ]
        );
    }

    /**
     * @param int $customerGroupId
     * @param int|null $store
     * @param float $minSaleQty
     * @param float|null $result
     * @dataProvider getConfigValueDataProvider
     */
    public function testGetConfigValue($customerGroupId, $store, $minSaleQty, $result, $minSaleQtyDecoded = null)
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                $this->equalTo(\Magento\CatalogInventory\Model\Configuration::XML_PATH_MIN_SALE_QTY),
                $this->equalTo(\Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                $this->equalTo($store)
            )
            ->will($this->returnValue($minSaleQty));

        $this->serializerMock->expects($this->exactly($minSaleQtyDecoded ? 1 : 0))
            ->method('unserialize')
            ->with($minSaleQty)
            ->willReturn($minSaleQtyDecoded);

        $this->assertSame($result, $this->minsaleqty->getConfigValue($customerGroupId, $store));
    }

    /**
     * @return array
     */
    public function getConfigValueDataProvider()
    {
        return [
            'valid numeric' => [1, 2, '20', 20.],
            'null global group' => [0, null, '', null],
            'null retailer group' => [3, null, '', null],
            'valid serialized - wholesale group' => [
                2,
                1,
                '{"1":20.5,"2":34.2}',
                34.2,
                [
                    1 => 20.5,
                    2 => 34.2
                ]
            ],
            'valid serialized - general group' => [
                1,
                44,
                '{"1":20.5,"2":34.2}',
                20.5,
                [
                    1 => 20.5,
                    2 => 34.2
                ]
            ],
            // custom group_id matches id in config
            'valid serialized - custom group match' => [
                5,
                4,
                '[{"customer_group_id":5,"min_sale_qty":40.10000000000}]',
                40.1,
                [
                    [
                        'customer_group_id' => 5,
                        'min_sale_qty' => 40.1
                    ]
                ]
            ],
            // scenario where group_id doesn't match an id in the config
            // calls getAllCustomersGroupId method, which will return the all customers group id and match
            'valid serialized - custom group no match' => [
                5,
                4,
                '[{"customer_group_id":32000,"min_sale_qty":2.5}]',
                2.5,
                [
                    [
                        'customer_group_id' => 32000,
                        'min_sale_qty' => 2.5
                    ]
                ]
            ]
        ];
    }

    /**
     * @param string|array $value
     * @param array $result
     * @param int $serializeCallCount
     * @dataProvider makeArrayFieldValueDataProvider
     */
    public function testMakeArrayFieldValue($value, $result, $serializeCallCount = 0)
    {
        $this->serializerMock->expects($this->exactly($serializeCallCount))
            ->method('unserialize')
            ->with($value)
            ->willReturn($result);

        $this->assertSame($result, $this->minsaleqty->makeArrayFieldValue($value));
    }

    /**
     * @return array
     */
    public function makeArrayFieldValueDataProvider()
    {
        return [
            'empty string' => ['', []],
            'valid with getAllCustomersGroupId lookup' => [
                '20',
                [
                    'unique_hash' => [
                        'customer_group_id' => 32000, 'min_sale_qty' => 20.
                    ]
                ]
            ],
            'valid with unserialize' => [
                '[{"customer_group_id":32000,"min_sale_qty":2.5}]',
                [
                    ['customer_group_id' => 32000, 'min_sale_qty' => 2.5]
                ],
                1
            ],
        ];
    }

    /**
     * @param string|array $value
     * @param string $result
     * @param int $serializeCallCount
     * @param null|array $decodedValue
     * @dataProvider makeStorableArrayFieldValueDataProvider
     */
    public function testMakeStorableArrayFieldValue($value, $result, $serializeCallCount = 0, $decodedValue = null)
    {
        $this->serializerMock->expects($this->exactly($serializeCallCount))
            ->method('serialize')
            ->with($decodedValue ?: $value)
            ->willReturn($result);

        $this->assertSame($result, $this->minsaleqty->makeStorableArrayFieldValue($value));
    }

    /**
     * @return array
     */
    public function makeStorableArrayFieldValueDataProvider()
    {
        return [
            'invalid bool' => [false, ''],
            'invalid empty string' => ['', ''],
            'valid numeric' => ['22', '22'],
            'valid empty array' => [[], '[]', 1],
            'valid no key match' => [
                ['customer_group_id' => 32000, 'min_sale_qty' => 2.5],
                '{"customer_group_id":32000,"min_sale_qty":2.5}',
                1
            ],
            'valid key match' => [
                [['customer_group_id' => 32000, 'min_sale_qty' => 2.5]],
                '2.5'
            ],
            'valid wholesale' => [
                [['customer_group_id' => 2, 'min_sale_qty' => 2.5]],
                '{"2":2.5}',
                1,
                [2 => 2.5]
            ],
            'invalid - cannot override not logged in group' => [
                [['min_sale_qty' => 2.5]],
                '[1]',
                1,
                [0 => 1.0]
            ]
        ];
    }
}
