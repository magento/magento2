<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GetterSetterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $className
     * @param array $variables
     * @dataProvider dataProviderGettersSetters
     */
    public function testGettersSetters($className = null, $variables = null)
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $classObject = $objectManager->getObject($className);

        foreach ($variables as $variableName => $variableValue) {
            $setterName = 'set' . $variableName;

            $this->assertTrue(
                method_exists($classObject, $setterName),
                "Method " . $setterName . " does not exist in " . $className
            );

            if (is_array($variableValue)) {
                if (strpos($variableValue[0], 'Magento') !== false) {
                    $obj = $objectManager->getObject($variableValue[0]);
                    $variableValue = [$obj];
                    $variables[$variableName] = $variableValue;
                }
            } elseif (strpos($variableValue, 'Magento') !== false) {
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
            [\Magento\Tax\Model\TaxDetails\AppliedTax::class,
                [
                    'TaxRateKey' => 'taxRateKey',
                    'Percent' => 1.0,
                    'Amount' => 1.0,
                    'Rates' => [\Magento\Tax\Model\TaxDetails\AppliedTaxRate::class
                        ],
                    'ExtensionAttributes' => \Magento\Tax\Api\Data\AppliedTaxExtension::class
                ]
            ],
            [\Magento\Tax\Model\TaxDetails\AppliedTaxRate::class,
                [
                    'Code' => 'code',
                    'Title' => 'title',
                    'Percent' => 1.0,
                    'ExtensionAttributes' => \Magento\Tax\Api\Data\AppliedTaxRateExtension::class
                ]
            ],
            [\Magento\Tax\Model\Sales\Order\Tax::class,
                [
                    'Code' => 'code',
                    'Title' => 'title',
                    'Percent' => 1.0,
                    'Amount' => 'amount',
                    'BaseAmount' => 'baseAmount',
                    'ExtensionAttributes' => \Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxExtension::class
                ]
            ],
            [\Magento\Tax\Model\Sales\Order\Details::class,
                [
                    'AppliedTaxes' => [\Magento\Tax\Model\Sales\Order\Tax::class
                        ],
                    'Items' => [\Magento\Sales\Model\Order\Tax\Item::class
                        ],
                    'ExtensionAttributes' => \Magento\Tax\Api\Data\OrderTaxDetailsExtension::class
                ]
            ],
            [\Magento\Sales\Model\Order\Tax\Item::class,
                [
                    'Type' => 'type',
                    'ItemId' => 1,
                    'AssociatedItemId' => 1,
                    'AppliedTaxes' => [\Magento\Tax\Model\Sales\Order\Tax::class
                        ],
                    'ExtensionAttributes' => \Magento\Tax\Api\Data\OrderTaxDetailsItemExtension::class
                ]
            ],
            [\Magento\Tax\Model\Sales\Quote\QuoteDetails::class,
                [
                    'BillingAddress' => \Magento\Customer\Model\Data\Address::class,
                    'ShippingAddress' => \Magento\Customer\Model\Data\Address::class,
                    'CustomerTaxClassKey' => \Magento\Tax\Model\TaxClass\Key::class,
                    'CustomerId' => 1,
                    'Items' => [\Magento\Sales\Model\Order\Tax\Item::class
                        ],
                    'CustomerTaxClassId' => 1,
                    'ExtensionAttributes' => \Magento\Tax\Api\Data\QuoteDetailsExtension::class
                ]
            ],
            [\Magento\Tax\Model\Sales\Quote\ItemDetails::class,
                [
                    'Code' => 'code',
                    'Type' => 'type',
                    'TaxClassKey' => \Magento\Tax\Model\TaxClass\Key::class,
                    'UnitPrice' => 1.0,
                    'Quantity' => 1.0,
                    'IsTaxIncluded' => true,
                    'ShortDescription' => 'shortDescription',
                    'DiscountAmount' => 1.0,
                    'ParentCode' => 'parentCode',
                    'AssociatedItemCode' => 1,
                    'TaxClassId' => 1,
                    'ExtensionAttributes' => \Magento\Tax\Api\Data\QuoteDetailsItemExtension::class
                ]
            ],
            [\Magento\Tax\Model\ClassModel::class,
                [
                    'ClassId' => 1,
                    'ClassName' => 'className',
                    'ClassType' => 'classType',
                    'ExtensionAttributes' => \Magento\Tax\Api\Data\TaxClassExtension::class
                ]
            ],
            [\Magento\Tax\Model\TaxClass\Key::class,
                [
                    'Type' => 'type',
                    'Value' => 'value',
                    'ExtensionAttributes' => \Magento\Tax\Api\Data\TaxClassKeyExtension::class
                ]
            ],
            [\Magento\Tax\Model\TaxDetails\TaxDetails::class,
                [
                    'Subtotal' => 1.0,
                    'TaxAmount' => 1.0,
                    'DiscountTaxCompensationAmount' => 1.0,
                    'AppliedTaxes' => [\Magento\Tax\Model\TaxDetails\AppliedTax::class
                        ],
                    'Items' => [\Magento\Tax\Model\TaxDetails\ItemDetails::class
                        ],
                    'ExtensionAttributes' => \Magento\Tax\Api\Data\TaxDetailsExtension::class
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
                    'AppliedTaxes' => [\Magento\Tax\Model\TaxDetails\AppliedTax::class
                        ],
                    'AssociatedItemCode' => 1,
                    'ExtensionAttributes' => \Magento\Tax\Api\Data\TaxDetailsItemExtension::class
                ]
            ],
            [\Magento\Tax\Model\Calculation\Rate::class,
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
                    'Titles' => [\Magento\Tax\Model\Calculation\Rate\Title::class
                        ],
                    'ExtensionAttributes' => \Magento\Tax\Api\Data\TaxRateExtension::class
                ]
            ],
            [\Magento\Tax\Model\Calculation\Rate\Title::class,
                [
                    'StoreId' => 'storeId',
                    'Value' => 'value',
                    'ExtensionAttributes' => \Magento\Tax\Api\Data\TaxRateTitleExtension::class
                ]
            ],
            [\Magento\Tax\Model\Calculation\Rule::class,
                [
                    'Id' => 1,
                    'Code' => 'code',
                    'Priority' => 1,
                    'Position' => 1,
                    'CustomerTaxClassIds' => [1],
                    'ProductTaxClassIds' => [1],
                    'TaxRateIds' => [1],
                    'CalculateSubtotal' => true,
                    'ExtensionAttributes' => \Magento\Tax\Api\Data\TaxRuleExtension::class
                ]
            ]
        ];
    }
}
