<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit;

use Magento\Customer\Model\Data\Address;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order\Tax\Item;
use Magento\Tax\Api\Data\AppliedTaxExtension;
use Magento\Tax\Api\Data\AppliedTaxRateExtension;
use Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxExtension;
use Magento\Tax\Api\Data\OrderTaxDetailsExtension;
use Magento\Tax\Api\Data\OrderTaxDetailsItemExtension;
use Magento\Tax\Api\Data\QuoteDetailsExtension;
use Magento\Tax\Api\Data\QuoteDetailsItemExtension;
use Magento\Tax\Api\Data\TaxClassExtension;
use Magento\Tax\Api\Data\TaxClassKeyExtension;
use Magento\Tax\Api\Data\TaxDetailsExtension;
use Magento\Tax\Api\Data\TaxDetailsItemExtension;
use Magento\Tax\Api\Data\TaxRateExtension;
use Magento\Tax\Api\Data\TaxRateTitleExtension;
use Magento\Tax\Api\Data\TaxRuleExtension;
use Magento\Tax\Model\Calculation\Rate;
use Magento\Tax\Model\Calculation\Rate\Title;
use Magento\Tax\Model\Calculation\Rule;
use Magento\Tax\Model\ClassModel;
use Magento\Tax\Model\Sales\Order\Details;
use Magento\Tax\Model\Sales\Order\Tax;
use Magento\Tax\Model\Sales\Quote\ItemDetails;
use Magento\Tax\Model\Sales\Quote\QuoteDetails;
use Magento\Tax\Model\TaxClass\Key;
use Magento\Tax\Model\TaxDetails\AppliedTax;
use Magento\Tax\Model\TaxDetails\AppliedTaxRate;
use Magento\Tax\Model\TaxDetails\TaxDetails;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GetterSetterTest extends TestCase
{
    /**
     * @param string $className
     * @param array $variables
     * @dataProvider dataProviderGettersSetters
     */
    public function testGettersSetters($className = null, $variables = null)
    {
        $objectManager = new ObjectManager($this);
        $classObject = $objectManager->getObject($className);

        foreach ($variables as $variableName => $variableValue) {
            $setterName = 'set' . $variableName;

            $this->assertTrue(
                method_exists($classObject, $setterName),
                "Method " . $setterName . " does not exist in " . $className
            );

            if (is_array($variableValue)) {
                if (strpos((string)$variableValue[0], 'Magento') !== false) {
                    $obj = $objectManager->getObject($variableValue[0]);
                    $variableValue = [$obj];
                    $variables[$variableName] = $variableValue;
                }
            } elseif (strpos((string)$variableValue, 'Magento') !== false) {
                $obj = $objectManager->getObject($variableValue);
                $variableValue = $obj;
                $variables[$variableName] = $variableValue;
            }
            $this->assertNotFalse(
                call_user_func(
                    [$classObject, $setterName],
                    $variableValue
                ),
                "Calling method " . $setterName . " failed in " . $className
            );
        }

        foreach ($variables as $variableName => $variableValue) {
            $getterName = 'get' . $variableName;

            $this->assertTrue(
                method_exists($classObject, $getterName),
                "Method " . $getterName . " does not exist in " . $className
            );
            $result = call_user_func([$classObject, $getterName]);
            $this->assertNotFalse(
                $result,
                "Calling method " . $getterName . " failed in " . $className
            );
            $this->assertSame(
                $result,
                $variableValue,
                "Value from " . $getterName . "did not match in " . $className
            );
        }
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function dataProviderGettersSetters()
    {
        // Test each class that implements the Tax Api Data Interfaces
        return [
            [AppliedTax::class,
                [
                    'TaxRateKey' => 'taxRateKey',
                    'Percent' => 1.0,
                    'Amount' => 1.0,
                    'Rates' => [AppliedTaxRate::class
                    ],
                    'ExtensionAttributes' => AppliedTaxExtension::class
                ]
            ],
            [AppliedTaxRate::class,
                [
                    'Code' => 'code',
                    'Title' => 'title',
                    'Percent' => 1.0,
                    'ExtensionAttributes' => AppliedTaxRateExtension::class
                ]
            ],
            [Tax::class,
                [
                    'Code' => 'code',
                    'Title' => 'title',
                    'Percent' => 1.0,
                    'Amount' => 'amount',
                    'BaseAmount' => 'baseAmount',
                    'ExtensionAttributes' => OrderTaxDetailsAppliedTaxExtension::class
                ]
            ],
            [Details::class,
                [
                    'AppliedTaxes' => [Tax::class
                    ],
                    'Items' => [Item::class
                    ],
                    'ExtensionAttributes' => OrderTaxDetailsExtension::class
                ]
            ],
            [Item::class,
                [
                    'Type' => 'type',
                    'ItemId' => 1,
                    'AssociatedItemId' => 1,
                    'AppliedTaxes' => [Tax::class
                    ],
                    'ExtensionAttributes' => OrderTaxDetailsItemExtension::class
                ]
            ],
            [QuoteDetails::class,
                [
                    'BillingAddress' => Address::class,
                    'ShippingAddress' => Address::class,
                    'CustomerTaxClassKey' => Key::class,
                    'CustomerId' => 1,
                    'Items' => [Item::class
                    ],
                    'CustomerTaxClassId' => 1,
                    'ExtensionAttributes' => QuoteDetailsExtension::class
                ]
            ],
            [ItemDetails::class,
                [
                    'Code' => 'code',
                    'Type' => 'type',
                    'TaxClassKey' => Key::class,
                    'UnitPrice' => 1.0,
                    'Quantity' => 1.0,
                    'IsTaxIncluded' => true,
                    'ShortDescription' => 'shortDescription',
                    'DiscountAmount' => 1.0,
                    'ParentCode' => 'parentCode',
                    'AssociatedItemCode' => 1,
                    'TaxClassId' => 1,
                    'ExtensionAttributes' => QuoteDetailsItemExtension::class
                ]
            ],
            [ClassModel::class,
                [
                    'ClassId' => 1,
                    'ClassName' => 'className',
                    'ClassType' => 'classType',
                    'ExtensionAttributes' => TaxClassExtension::class
                ]
            ],
            [Key::class,
                [
                    'Type' => 'type',
                    'Value' => 'value',
                    'ExtensionAttributes' => TaxClassKeyExtension::class
                ]
            ],
            [TaxDetails::class,
                [
                    'Subtotal' => 1.0,
                    'TaxAmount' => 1.0,
                    'DiscountTaxCompensationAmount' => 1.0,
                    'AppliedTaxes' => [AppliedTax::class
                    ],
                    'Items' => [\Magento\Tax\Model\TaxDetails\ItemDetails::class
                    ],
                    'ExtensionAttributes' => TaxDetailsExtension::class
                ]
            ],
            [\Magento\Tax\Model\TaxDetails\ItemDetails::class,
                [
                    'Code' => 'code',
                    'Type' => 'type',
                    'TaxPercent' => 1.0,
                    'Price' => 1.0,
                    'PriceInclTax' => 1.0,
                    'RowTotal' => 1.0,
                    'RowTotalInclTax' => 1.0,
                    'RowTax' => 1.0,
                    'TaxableAmount' => 1.0,
                    'DiscountAmount' => 1.0,
                    'DiscountTaxCompensationAmount' => 1.0,
                    'AppliedTaxes' => [AppliedTax::class
                    ],
                    'AssociatedItemCode' => 1,
                    'ExtensionAttributes' => TaxDetailsItemExtension::class
                ]
            ],
            [Rate::class,
                [
                    'Id' => 1,
                    'TaxCountryId' => 'taxCountryId',
                    'TaxRegionId' => 1,
                    'RegionName' => 'regionName',
                    'TaxPostcode' => 'taxPostCode',
                    'ZipIsRange' => 1,
                    'ZipFrom' => 1,
                    'ZipTo' => 1,
                    'Rate' => 1.0,
                    'Code' => 'code',
                    'Titles' => [Title::class
                    ],
                    'ExtensionAttributes' => TaxRateExtension::class
                ]
            ],
            [Title::class,
                [
                    'StoreId' => 'storeId',
                    'Value' => 'value',
                    'ExtensionAttributes' => TaxRateTitleExtension::class
                ]
            ],
            [Rule::class,
                [
                    'Id' => 1,
                    'Code' => 'code',
                    'Priority' => 1,
                    'Position' => 1,
                    'CustomerTaxClassIds' => [1],
                    'ProductTaxClassIds' => [1],
                    'TaxRateIds' => [1],
                    'CalculateSubtotal' => true,
                    'ExtensionAttributes' => TaxRuleExtension::class
                ]
            ]
        ];
    }
}
