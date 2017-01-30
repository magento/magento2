<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit;

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
            } else if (strpos($variableValue, 'Magento') !== false) {
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
            [
                'Magento\Tax\Model\TaxDetails\AppliedTax',
                [
                    'TaxRateKey' => 'taxRateKey',
                    'Percent' => 1.0,
                    'Amount' => 1.0,
                    'Rates' =>
                        [
                            'Magento\Tax\Model\TaxDetails\AppliedTaxRate'
                        ],
                    'ExtensionAttributes' => 'Magento\Tax\Api\Data\AppliedTaxExtension'
                ]
            ],
            [
                'Magento\Tax\Model\TaxDetails\AppliedTaxRate',
                [
                    'Code' => 'code',
                    'Title' => 'title',
                    'Percent' => 1.0,
                    'ExtensionAttributes' => 'Magento\Tax\Api\Data\AppliedTaxRateExtension'
                ]
            ],
            [
                'Magento\Tax\Model\Sales\Order\Tax',
                [
                    'Code' => 'code',
                    'Title' => 'title',
                    'Percent' => 1.0,
                    'Amount' => 'amount',
                    'BaseAmount' => 'baseAmount',
                    'ExtensionAttributes' => 'Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxExtension'
                ]
            ],
            [
                'Magento\Tax\Model\Sales\Order\Details',
                [
                    'AppliedTaxes' =>
                        [
                            'Magento\Tax\Model\Sales\Order\Tax'
                        ],
                    'Items' =>
                        [
                            'Magento\Sales\Model\Order\Tax\Item'
                        ],
                    'ExtensionAttributes' => 'Magento\Tax\Api\Data\OrderTaxDetailsExtension'
                ]
            ],
            [
                'Magento\Sales\Model\Order\Tax\Item',
                [
                    'Type' => 'type',
                    'ItemId' => 1,
                    'AssociatedItemId' => 1,
                    'AppliedTaxes' =>
                        [
                            'Magento\Tax\Model\Sales\Order\Tax'
                        ],
                    'ExtensionAttributes' => 'Magento\Tax\Api\Data\OrderTaxDetailsItemExtension'
                ]
            ],
            [
                'Magento\Tax\Model\Sales\Quote\QuoteDetails',
                [
                    'BillingAddress' => 'Magento\Customer\Model\Data\Address',
                    'ShippingAddress' => 'Magento\Customer\Model\Data\Address',
                    'CustomerTaxClassKey' => 'Magento\Tax\Model\TaxClass\Key',
                    'CustomerId' => 1,
                    'Items' =>
                        [
                            'Magento\Sales\Model\Order\Tax\Item'
                        ],
                    'CustomerTaxClassId' => 1,
                    'ExtensionAttributes' => 'Magento\Tax\Api\Data\QuoteDetailsExtension'
                ]
            ],
            [
                'Magento\Tax\Model\Sales\Quote\ItemDetails',
                [
                    'Code' => 'code',
                    'Type' => 'type',
                    'TaxClassKey' => 'Magento\Tax\Model\TaxClass\Key',
                    'UnitPrice' => 1.0,
                    'Quantity' => 1.0,
                    'IsTaxIncluded' => true,
                    'ShortDescription' => 'shortDescription',
                    'DiscountAmount' => 1.0,
                    'ParentCode' => 'parentCode',
                    'AssociatedItemCode' => 1,
                    'TaxClassId' => 1,
                    'ExtensionAttributes' => '\Magento\Tax\Api\Data\QuoteDetailsItemExtension'
                ]
            ],
            [
                'Magento\Tax\Model\ClassModel',
                [
                    'ClassId' => 1,
                    'ClassName' => 'className',
                    'ClassType' => 'classType',
                    'ExtensionAttributes' => '\Magento\Tax\Api\Data\TaxClassExtension'
                ]
            ],
            [
                'Magento\Tax\Model\TaxClass\Key',
                [
                    'Type' => 'type',
                    'Value' => 'value',
                    'ExtensionAttributes' => '\Magento\Tax\Api\Data\TaxClassKeyExtension'
                ]
            ],
            [
                'Magento\Tax\Model\TaxDetails\TaxDetails',
                [
                    'Subtotal' => 1.0,
                    'TaxAmount' => 1.0,
                    'DiscountTaxCompensationAmount' => 1.0,
                    'AppliedTaxes' =>
                        [
                            'Magento\Tax\Model\TaxDetails\AppliedTax'
                        ],
                    'Items' =>
                        [
                            'Magento\Tax\Model\TaxDetails\ItemDetails'
                        ],
                    'ExtensionAttributes' => '\Magento\Tax\Api\Data\TaxDetailsExtension'
                ]
            ],
            [
                'Magento\Tax\Model\TaxDetails\ItemDetails',
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
                    'AppliedTaxes' =>
                        [
                            'Magento\Tax\Model\TaxDetails\AppliedTax'
                        ],
                    'AssociatedItemCode' => 1,
                    'ExtensionAttributes' => '\Magento\Tax\Api\Data\TaxDetailsItemExtension'
                ]
            ],
            [
                'Magento\Tax\Model\Calculation\Rate',
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
                    'Titles' =>
                        [
                            'Magento\Tax\Model\Calculation\Rate\Title'
                        ],
                    'ExtensionAttributes' => '\Magento\Tax\Api\Data\TaxRateExtension'
                ]
            ],
            [
                'Magento\Tax\Model\Calculation\Rate\Title',
                [
                    'StoreId' => 'storeId',
                    'Value' => 'value',
                    'ExtensionAttributes' => '\Magento\Tax\Api\Data\TaxRateTitleExtension'
                ]
            ],
            [
                'Magento\Tax\Model\Calculation\Rule',
                [
                    'Id' => 1,
                    'Code' => 'code',
                    'Priority' => 1,
                    'Position' => 1,
                    'CustomerTaxClassIds' => [1],
                    'ProductTaxClassIds' => [1],
                    'TaxRateIds' => [1],
                    'CalculateSubtotal' => true,
                    'ExtensionAttributes' => '\Magento\Tax\Api\Data\TaxRuleExtension'
                ]
            ]
        ];
    }
}
