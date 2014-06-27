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

namespace Magento\Tax\Service\V1\Data\TaxDetails;

class ItemBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManager
     */
    private $objectManager;

    /**
     * Applied Tax data object builder
     *
     * @var \Magento\Tax\Service\V1\Data\TaxDetails\AppliedTaxBuilder
     */
    private $appliedTaxBuilder;

    /**
     * Tax Details Item data object builder
     *
     * @var \Magento\Tax\Service\V1\Data\TaxDetails\ItemBuilder
     */
    private $taxDetailsItemBuilder;

    private static $travisCtyRateObjectDataArray = [
        'code' => '9',
        'title' => 'TX-TRAVIS',
        'percent' => 0.0825,
    ];

    private static $utopiaCtyRateObjectDataArray = [
        'code' => '2',
        'title' => 'TX-UTOPIA',
        'percent' => 0.01,
    ];

    private static $noTaxRateObjectDataArray = [
        'code' => '5',
        'title' => 'TX-FREE',
        'percent' => 0.00,
    ];

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->appliedTaxBuilder = $this->objectManager
            ->create('\Magento\Tax\Service\V1\Data\TaxDetails\AppliedTaxBuilder');
        $this->taxDetailsItemBuilder = $this->objectManager
            ->create('\Magento\Tax\Service\V1\Data\TaxDetails\ItemBuilder');
    }

    public function testTaxDetailsNoItemTax()
    {
        $appliedTaxDataArray = [
            'tax_rate_key' => '0',
            'percent' => 0.00,
            'amount' => 0.00,
            'rates' => [
                $this::$noTaxRateObjectDataArray,
            ],
        ];

        $taxDetailsItemDataArray = [
            'code' => 'FOO123',
            'type' => 'product',
            'tax_percent' => 0.00,
            'price' => 9.99,
            'price_incl_tax' => 9.99,
            'row_total' => 9.99,
            'row_tax' => 0.00,
            'taxable_amount' => 9.99,
            'discount_amount' => 0.00,
            'discount_tax_compensation_amount' => 0.00,
            'applied_taxes' => [
                $appliedTaxDataArray
            ],
        ];

        $taxDetailsItemObjectFromArray = $this->taxDetailsItemBuilder
            ->populateWithArray($taxDetailsItemDataArray)
            ->create();

        $taxDetailsItemObjectFromObject = $this->taxDetailsItemBuilder
            ->populate($taxDetailsItemObjectFromArray)
            ->create();

        $this->assertEquals(
            $taxDetailsItemDataArray,
            $taxDetailsItemObjectFromArray->__toArray()
        );

        $this->assertEquals(
            $taxDetailsItemDataArray,
            $taxDetailsItemObjectFromObject->__toArray()
        );

        $this->assertEquals(
            $taxDetailsItemObjectFromArray,
            $taxDetailsItemObjectFromObject
        );
    }

    public function testTaxDetailsItemSingleTax()
    {
        $appliedTaxDataArray = [
            'tax_rate_key' => '1',
            'percent' => 0.0825,
            'amount' => 0.82,
            'rates' => [
                self::$travisCtyRateObjectDataArray,
            ],
        ];

        $taxDetailsItemDataArray = [
            'code' => 'ABC123',
            'type' => 'product',
            'tax_percent' => 0.0825,
            'price' => 9.99,
            'price_incl_tax' => 10.81,
            'row_total' => 9.99,
            'row_tax' => 0.82,
            'taxable_amount' => 9.99,
            'discount_amount' => 0.00,
            'discount_tax_compensation_amount' => 0.00,
            'applied_taxes' => [
                $appliedTaxDataArray
            ],
        ];

        $taxDetailsItemObjectFromArray = $this->taxDetailsItemBuilder
            ->populateWithArray($taxDetailsItemDataArray)
            ->create();

        $taxDetailsItemObjectFromObject = $this->taxDetailsItemBuilder
            ->populate($taxDetailsItemObjectFromArray)
            ->create();

        $this->assertEquals(
            $taxDetailsItemDataArray,
            $taxDetailsItemObjectFromArray->__toArray()
        );

        $this->assertEquals(
            $taxDetailsItemDataArray,
            $taxDetailsItemObjectFromObject->__toArray()
        );

        $this->assertEquals(
            $taxDetailsItemObjectFromArray,
            $taxDetailsItemObjectFromObject
        );
    }

    public function testTaxDetailsItemMultipleTaxes()
    {
        $appliedTaxDataArray = [
            'tax_rate_key' => '77',
            'percent' => 0.0925,
            'amount' => 0.82,
            'rates' => [
                self::$travisCtyRateObjectDataArray,
                self::$utopiaCtyRateObjectDataArray,
                self::$noTaxRateObjectDataArray,
            ],
        ];

        $taxDetailsItemDataArray = [
            'code' => 'QWERTY123',
            'type' => 'product',
            'tax_percent' => 0.0925,
            'price' => 4.99,
            'price_incl_tax' => 5.45,
            'row_total' => 4.99,
            'row_tax' => 0.64,
            'taxable_amount' => 4.99,
            'discount_amount' => 0.00,
            'discount_tax_compensation_amount' => 0.00,
            'applied_taxes' => [
                $appliedTaxDataArray
            ],
        ];

        $taxDetailsItemObjectFromArray = $this->taxDetailsItemBuilder
            ->populateWithArray($taxDetailsItemDataArray)
            ->create();

        $taxDetailsItemObjectFromObject = $this->taxDetailsItemBuilder
            ->populate($taxDetailsItemObjectFromArray)
            ->create();

        $this->assertEquals(
            $taxDetailsItemDataArray,
            $taxDetailsItemObjectFromArray->__toArray()
        );

        $this->assertEquals(
            $taxDetailsItemDataArray,
            $taxDetailsItemObjectFromObject->__toArray()
        );

        $this->assertEquals(
            $taxDetailsItemObjectFromArray,
            $taxDetailsItemObjectFromObject
        );
    }
}
