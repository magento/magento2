<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\CatalogInventory\Helper;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

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

    protected function setUp()
    {
        $this->scopeConfigMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->randomMock = $this->getMock('Magento\Framework\Math\Random');
        $this->randomMock->expects($this->any())
            ->method('getUniqueHash')
            ->with($this->equalTo('_'))
            ->will($this->returnValue('unique_hash'));

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->minsaleqty = $this->objectManagerHelper->getObject(
            'Magento\CatalogInventory\Helper\Minsaleqty',
            [
                'scopeConfig' => $this->scopeConfigMock,
                'mathRandom' => $this->randomMock
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
    public function testGetConfigValue($customerGroupId, $store, $minSaleQty, $result)
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                $this->equalTo(\Magento\CatalogInventory\Model\Stock\Item::XML_PATH_MIN_SALE_QTY),
                $this->equalTo(\Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                $this->equalTo($store)
            )
            ->will($this->returnValue($minSaleQty));
        $this->assertSame($result, $this->minsaleqty->getConfigValue($customerGroupId, $store));
    }

    /**
     * @return array
     */
    public function getConfigValueDataProvider()
    {
        return [
            [1, 2, '20', 20.],
            [0, null, '', null],
            [3, null, '', null],
            [2, 1, 'a:2:{i:1;s:4:"20.5";i:2;s:4:"34.2";}', 34.2],
            [1, 44, 'a:2:{i:1;s:4:"20.5";i:2;s:4:"34.2";}', 20.5],
            [5, 4, 'a:1:{i:0;a:2:{s:17:"customer_group_id";i:5;s:12:"min_sale_qty";d:40.10000000000000;}}', 40.1],
            [5, 4, 'a:1:{i:0;a:2:{s:17:"customer_group_id";i:32000;s:12:"min_sale_qty";d:2.5;}}', 2.5],
        ];
    }

    /**
     * @param string|array $value
     * @param array $result
     * @dataProvider makeArrayFieldValueDataProvider
     */
    public function testMakeArrayFieldValue($value, $result)
    {
        $this->assertSame($result, $this->minsaleqty->makeArrayFieldValue($value));
    }

    /**
     * @return array
     */
    public function makeArrayFieldValueDataProvider()
    {
        return [
            ['', []],
            ['20', ['unique_hash' => ['customer_group_id' => 32000, 'min_sale_qty' => 20.]]],
            [
                'a:1:{i:0;a:2:{s:17:"customer_group_id";i:32000;s:12:"min_sale_qty";d:2.5;}} ',
                [['customer_group_id' => 32000, 'min_sale_qty' => 2.5]]
            ],
        ];
    }

    /**
     * @param string|array $value
     * @param string $result
     * @dataProvider makeStorableArrayFieldValueDataProvider
     */
    public function testMakeStorableArrayFieldValue($value, $result)
    {
        $this->assertSame($result, $this->minsaleqty->makeStorableArrayFieldValue($value));
    }

    /**
     * @return array
     */
    public function makeStorableArrayFieldValueDataProvider()
    {
        return [
            [false, ''],
            ['', ''],
            ['22', '22'],
            [[], 'a:0:{}'],
            [
                ['customer_group_id' => 32000, 'min_sale_qty' => 2.5],
                'a:2:{s:17:"customer_group_id";d:32000;s:12:"min_sale_qty";d:2.5;}'
            ],
            [
                [['customer_group_id' => 32000, 'min_sale_qty' => 2.5]],
                '2.5'
            ],
            [
                [['customer_group_id' => 2, 'min_sale_qty' => 2.5]],
                'a:1:{i:2;d:2.5;}'
            ],
            [
                [['min_sale_qty' => 2.5]],
                'a:1:{i:0;d:1;}'
            ],
        ];
    }
}
