<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model\System\Config\Backend;

use Magento\TestFramework\Helper\Bootstrap;

class MinsaleqtyTest extends \PHPUnit_Framework_TestCase
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
     * @param $value
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
        $indexedConfig = array_values($hashedConfig);
        $this->assertEquals($decodedExpectedValue, $indexedConfig);
    }

    /**
     * @return array
     */
    public function saveAndLoadDataProvider()
    {
        return [
            'bool' => [false, '', []],
            'empty string' => ['', '', []],
            'empty array' => [[], '[]', []],
            'valid numeric - global group' => [
                '22',
                '22',
                [
                    [
                        'customer_group_id' => 32000,
                        'min_sale_qty' => 22
                    ]
                ]
            ],
            'invalid array' => [
                ['customer_group_id' => 32000, 'min_sale_qty' => 2.5],
                '{"customer_group_id":32000,"min_sale_qty":2.5}',
                [
                    0 => [
                        'customer_group_id' => 'customer_group_id',
                        'min_sale_qty' => 32000
                    ],
                    1 => [
                        'customer_group_id' => 'min_sale_qty',
                        'min_sale_qty' => 2.5
                    ]
                ]
            ],
            'valid array - global group' => [
                [['customer_group_id' => 32000, 'min_sale_qty' => 2.5]],
                '2.5',
                [
                    0 => [
                        'customer_group_id' => 32000,
                        'min_sale_qty' => 2.5
                    ]
                ]
            ],
            'valid wholesale' => [
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
                [0 => ['min_sale_qty' => 2.5]],
                '[1]',
                [
                    0 => [
                        'customer_group_id' => 0,
                        'min_sale_qty' => 1
                    ]
                ]
            ]
        ];
    }
}
