<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model\System\Config\Backend;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Customer\Api\GroupManagementInterface;

class MinsaleqtyTest extends \PHPUnit\Framework\TestCase
{
    /** @var Minsaleqty */
    private $minSaleQtyConfig;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->minSaleQtyConfig = $objectManager->create(Minsaleqty::class);
        $this->minSaleQtyConfig->setPath('cataloginventory/item_options/min_sale_qty');
    }

    /**
     * Test save and load cycle for minimum sale quantity configuration values. If the passed value is
     * valid and non-numeric, it should be json encoded by the serializer, otherwise stored as-is. On load,
     * the data will be decoded (if needed) and restructured into a specific hashed array format.
     *
     * @param bool|string|array $value
     * @param string $encodedExpectedValue
     * @param array $decodedExpectedValue
     * @magentoDbIsolation enabled
     * @dataProvider saveAndLoadDataProvider
     */
    public function testSaveAndLoad($value, $encodedExpectedValue, array $decodedExpectedValue)
    {
        $this->minSaleQtyConfig->setValue($value);
        $this->minSaleQtyConfig->save();
        $this->assertEquals($encodedExpectedValue, $this->minSaleQtyConfig->getValue());

        $this->minSaleQtyConfig->load($this->minSaleQtyConfig->getId());
        $hashedConfig = $this->minSaleQtyConfig->getValue();

        if (!is_array($hashedConfig)) {
            $this->fail('Loaded value is not an array, skipping further validation');
        }

        $indexedConfig = array_values($hashedConfig);
        $this->assertEquals($decodedExpectedValue, $indexedConfig);
    }

    /**
     * @return array
     */
    public function saveAndLoadDataProvider()
    {
        $objectManager = Bootstrap::getObjectManager();
        $groupManagement = $objectManager->create(GroupManagementInterface::class);
        $allCustomersGroupID = $groupManagement->getAllCustomersGroup()->getId();
        $notLoggedInGroupID = $groupManagement->getNotLoggedInGroup()->getId();

        return [
            'bool' => [false, '', []],
            'empty string' => ['', '', []],
            'empty array' => [[], '[]', []],
            'valid numeric - all customer group' => [
                '22',
                '22',
                [
                    [
                        'customer_group_id' => $allCustomersGroupID,
                        'min_sale_qty' => 22
                    ]
                ]
            ],
            'invalid named group array' => [
                ['customer_group_id' => 1, 'min_sale_qty' => 2.5],
                '{"customer_group_id":1,"min_sale_qty":2.5}',
                [
                    0 => [
                        'customer_group_id' => 'customer_group_id',
                        'min_sale_qty' => 1
                    ],
                    1 => [
                        'customer_group_id' => 'min_sale_qty',
                        'min_sale_qty' => 2.5
                    ]
                ]
            ],
            'valid array - all customer group' => [
                [['customer_group_id' => $allCustomersGroupID, 'min_sale_qty' => 2.5]],
                '2.5',
                [
                    0 => [
                        'customer_group_id' => $allCustomersGroupID,
                        'min_sale_qty' => 2.5
                    ]
                ]
            ],
            'valid named group' => [
                [['customer_group_id' => 2, 'min_sale_qty' => 2.5]],
                '{"2":2.5}',
                [
                    0 => [
                        'customer_group_id' => 2,
                        'min_sale_qty' => 2.5
                    ]
                ]
            ],
            'invalid - cannot override not logged in group' => [
                [$notLoggedInGroupID => ['min_sale_qty' => 2.5]],
                '[1]',
                [
                    0 => [
                        'customer_group_id' => $notLoggedInGroupID,
                        'min_sale_qty' => 1
                    ]
                ]
            ]
        ];
    }
}
