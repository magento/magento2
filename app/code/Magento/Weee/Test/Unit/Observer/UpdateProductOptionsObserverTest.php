<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Weee\Model\Tax as WeeeDisplayConfig;
use Magento\Tax\Model\Config as TaxConfig;

class UpdateProductOptionsObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests the methods that rely on the ScopeConfigInterface object to provide their return values
     *
     * @param array $initialArray The initial array that specifies the set of additional options
     * @param bool $weeeEnabled Whether the Weee module is assumed to be enabled
     * @param int $weeeDisplay Which Weee display is configured
     * @param int $priceDisplay Values are: including tax, excluding tax, or both including and excluding tax
     * @param array $expectedArray The revised array of the additional options
     *
     * @dataProvider updateProductOptionsProvider
     */
    public function testUpdateProductOptions($initialArray, $weeeEnabled, $weeeDisplay, $priceDisplay, $expectedArray)
    {
        $configObj = new \Magento\Framework\DataObject(
            [
                'additional_options' => $initialArray,
            ]
        );

        $weeeObject1 = new \Magento\Framework\DataObject(
            [
                'code' => 'fpt1',
                'amount' => '15.0000',
            ]
        );

        $weeeObject2 = new \Magento\Framework\DataObject(
            [
                'code' => 'fpt2',
                'amount' => '7.0000',
            ]
        );

        $weeeHelper=$this->getMock(\Magento\Weee\Helper\Data::class, [], [], '', false);
        $weeeHelper->expects($this->any())
            ->method('isEnabled')
            ->will($this->returnValue($weeeEnabled));
        $weeeHelper->expects($this->any())
            ->method('isDisplayIncl')
            ->will($this->returnValue($weeeDisplay == WeeeDisplayConfig::DISPLAY_INCL));
        $weeeHelper->expects($this->any())
            ->method('isDisplayExclDescIncl')
            ->will($this->returnValue($weeeDisplay == WeeeDisplayConfig::DISPLAY_EXCL_DESCR_INCL));
        $weeeHelper->expects($this->any())
            ->method('isDisplayExcl')
            ->will($this->returnValue($weeeDisplay == WeeeDisplayConfig::DISPLAY_EXCL));
        $weeeHelper->expects($this->any())
            ->method('getWeeeAttributesForBundle')
            ->will($this->returnValue([['fpt1' => $weeeObject1], ['fpt1'=>$weeeObject1, 'fpt2'=>$weeeObject2]]));

        $taxHelper=$this->getMock(\Magento\Tax\Helper\Data::class, [], [], '', false);
        $taxHelper->expects($this->any())
            ->method('displayPriceExcludingTax')
            ->will($this->returnValue($priceDisplay == TaxConfig::DISPLAY_TYPE_EXCLUDING_TAX));
        $taxHelper->expects($this->any())
            ->method('priceIncludesTax')
            ->will($this->returnValue(true));

        $responseObject=$this->getMock(\Magento\Framework\Event\Observer::class, ['getResponseObject'], [], '', false);
        $responseObject->expects($this->any())
            ->method('getResponseObject')
            ->will($this->returnValue($configObj));

        $observerObject=$this->getMock(\Magento\Framework\Event\Observer::class, ['getEvent'], [], '', false);
        $observerObject->expects($this->any())
            ->method('getEvent')
            ->will($this->returnValue($responseObject));

        $product = $this->getMock(
            \Magento\Bundle\Model\Product\Type::class,
            ['getTypeId', 'getStoreId'],
            [],
            '',
            false
        );
        $product->expects($this->any())
            ->method('getStoreId')
            ->will($this->returnValue(1));
        $product->expects($this->any())
            ->method('getTypeId')
            ->will($this->returnValue('bundle'));

        $registry=$this->getMock(\Magento\Framework\Registry::class, [], [], '', false);
        $registry->expects($this->any())
            ->method('registry')
            ->with('current_product')
            ->will($this->returnValue($product));

        $objectManager = new ObjectManager($this);
        /** @var \Magento\Weee\Observer\UpdateProductOptionsObserver $weeeObserverObject */
        $weeeObserverObject = $objectManager->getObject(
            \Magento\Weee\Observer\UpdateProductOptionsObserver::class,
            [
                'weeeData' => $weeeHelper,
                'taxData' => $taxHelper,
                'registry' => $registry,
            ]
        );
        $weeeObserverObject->execute($observerObject);

        $this->assertEquals($expectedArray, $configObj->getData('additional_options'));
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function updateProductOptionsProvider()
    {
        return [
            'weee not enabled' => [
                'initialArray' => [
                    'TOTAL_BASE_CALCULATION' => 'TOTAL_BASE_CALCULATION',
                    'optionTemplate' => '<%= data.label %><% if (data.finalPrice.value) '
                        . '{ %> +<%- data.finalPrice.formatted %><% } %>',
                ],
                'weeeEnabled' => false,
                'weeeDisplay' => WeeeDisplayConfig::DISPLAY_INCL,         // has no effect for this scenario
                'priceDisplay' => TaxConfig::DISPLAY_TYPE_EXCLUDING_TAX,  // has no effect for this scenario
                'expectedArray' => [
                    'TOTAL_BASE_CALCULATION' => 'TOTAL_BASE_CALCULATION',
                    'optionTemplate' => '<%= data.label %><% if (data.finalPrice.value) '
                        . '{ %> +<%- data.finalPrice.formatted %><% } %>',
                ],
            ],

            'weee enabled, and display with Weee included in the price' => [
                'initialArray' => [
                    'TOTAL_BASE_CALCULATION' => 'TOTAL_BASE_CALCULATION',
                    'optionTemplate' => '<%= data.label %><% if (data.basePrice.value) '
                        . '{ %> +<%- data.basePrice.formatted %><% } %>',
                ],
                'weeeEnabled' => true,
                'weeeDisplay' => WeeeDisplayConfig::DISPLAY_INCL,
                'priceDisplay' => TaxConfig::DISPLAY_TYPE_INCLUDING_TAX,
                'expectedArray' => [
                    'TOTAL_BASE_CALCULATION' => 'TOTAL_BASE_CALCULATION',
                    'optionTemplate' => '<%= data.label %><% if (data.basePrice.value) '
                        . '{ %> +<%- data.basePrice.formatted %><% } %>',
                ],
            ],

            'weee enabled, and display with Weee included in the price, and include the Weee descriptions' => [
                'initialArray' => [
                    'TOTAL_BASE_CALCULATION' => 'TOTAL_BASE_CALCULATION',
                    'optionTemplate' => '<%= data.label %><% if (data.basePrice.value) '
                        . '{ %> +<%- data.basePrice.formatted %><% } %>',
                ],
                'weeeEnabled' => true,
                'weeeDisplay' => WeeeDisplayConfig::DISPLAY_INCL_DESCR,
                'priceDisplay' => TaxConfig::DISPLAY_TYPE_INCLUDING_TAX,
                'expectedArray' => [
                    'TOTAL_BASE_CALCULATION' => 'TOTAL_BASE_CALCULATION',
                    'optionTemplate' => '<%= data.label %><% if (data.basePrice.value) '
                        . '{ %> +<%- data.basePrice.formatted %><% } %> <% if (data.weeePricefpt1) '
                        . '{ %>  (: <%- data.weeePricefpt1.formatted %>)<% } %> '
                        . '<% if (data.weeePricefpt2) { %>  (: <%- data.weeePricefpt2.formatted %>)<% } %>',
                ],
            ],

            'weee enabled, and display with ExclDescIncl' => [
                'initialArray' => [
                    'TOTAL_BASE_CALCULATION' => 'TOTAL_BASE_CALCULATION',
                    'optionTemplate' => '<%= data.label %><% if (data.basePrice.value) '
                        . '{ %> +<%- data.basePrice.formatted %><% } %>',
                ],
                'weeeEnabled' => true,
                'weeeDisplay' => WeeeDisplayConfig::DISPLAY_EXCL_DESCR_INCL,
                'priceDisplay' => TaxConfig::DISPLAY_TYPE_INCLUDING_TAX,
                'expectedArray' => [
                    'TOTAL_BASE_CALCULATION' => 'TOTAL_BASE_CALCULATION',
                    'optionTemplate' => '<%= data.label %><% if (data.basePrice.value) '
                        . '{ %> +<%- data.basePrice.formatted %><% } %> <% if (data.weeePricefpt1) '
                        . '{ %>  (: <%- data.weeePricefpt1.formatted %>)<% } %> '
                        . '<% if (data.weeePricefpt2) { %>  (: <%- data.weeePricefpt2.formatted %>)<% } %> '
                        . '<% if (data.weeePrice) { %><%- data.weeePrice.formatted %><% } %>',
                ],
            ],

            'weee enabled, and display prices including tax but without Weee' => [
                'initialArray' => [
                    'TOTAL_BASE_CALCULATION' => 'TOTAL_BASE_CALCULATION',
                ],
                'weeeEnabled' => true,
                'weeeDisplay' => WeeeDisplayConfig::DISPLAY_EXCL,
                'priceDisplay' => TaxConfig::DISPLAY_TYPE_INCLUDING_TAX,
                'expectedArray' => [
                    'TOTAL_BASE_CALCULATION' => 'TOTAL_BASE_CALCULATION',
                    'optionTemplate' => '<%- data.label %><% if (data.finalPrice.value) '
                        . '{ %> +<%- data.finalPrice.formatted %><% } %>',
                ],
            ],

            'weee enabled, and display prices excluding tax but without Weee' => [
                'initialArray' => [
                    'TOTAL_BASE_CALCULATION' => 'TOTAL_BASE_CALCULATION',
                ],
                'weeeEnabled' => true,
                'weeeDisplay' => WeeeDisplayConfig::DISPLAY_EXCL,
                'priceDisplay' => TaxConfig::DISPLAY_TYPE_EXCLUDING_TAX,
                'expectedArray' => [
                    'TOTAL_BASE_CALCULATION' => 'TOTAL_BASE_CALCULATION',
                    'optionTemplate' => '<%- data.label %><% if (data.basePrice.value) '
                        . '{ %> +<%- data.basePrice.formatted %><% } %>',
                ],
            ],
        ];
    }
}
