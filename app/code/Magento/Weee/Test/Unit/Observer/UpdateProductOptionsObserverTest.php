<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Test\Unit\Observer;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class UpdateProductOptionsObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests the methods that rely on the ScopeConfigInterface object to provide their return values
     * @dataProvider updateProductOptionsProvider
     * @param array $testArray
     * @param bool $weeeDisplay
     * @param bool $weeeEnabled
     * @param array $expectedArray
     */
    public function testUpdateProductOptions($testArray, $weeeDisplay, $weeeEnabled, $expectedArray)
    {
        $configObj = new \Magento\Framework\DataObject(
            [
                'additional_options' => $testArray,
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
                'amount' => '15.0000',
            ]
        );

        $weeeHelper=$this->getMock('Magento\Weee\Helper\Data', [], [], '', false);
        $weeeHelper->expects($this->any())
            ->method('isEnabled')
            ->will($this->returnValue($weeeEnabled));

        $weeeHelper->expects($this->any())
            ->method('geDisplayExlDescIncl')
            ->will($this->returnValue($weeeDisplay));

        $weeeHelper->expects($this->any())
            ->method('getWeeeAttributesForBundle')
            ->will($this->returnValue([['fpt1' => $weeeObject1], ['fpt1'=>$weeeObject1, 'fpt2'=>$weeeObject2]]));

        $responseObject=$this->getMock('Magento\Framework\Event\Observer', ['getResponseObject'], [], '', false);
        $responseObject->expects($this->any())
            ->method('getResponseObject')
            ->will($this->returnValue($configObj));

        $observerObject=$this->getMock('Magento\Framework\Event\Observer', ['getEvent'], [], '', false);
        $observerObject->expects($this->any())
            ->method('getEvent')
            ->will($this->returnValue($responseObject));

        $product = $this->getMock(
            '\Magento\Bundle\Model\Product\Type',
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

        $registry=$this->getMock('Magento\Framework\Registry', [], [], '', false);
        $registry->expects($this->any())
            ->method('registry')
            ->with('current_product')
            ->will($this->returnValue($product));


        $objectManager = new ObjectManager($this);
        /** @var \Magento\Weee\Observer\UpdateProductOptionsObserver $weeeObserverObject */
        $weeeObserverObject = $objectManager->getObject(
            'Magento\Weee\Observer\UpdateProductOptionsObserver',
            [
                'weeeData' => $weeeHelper,
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
            [
                'testArray' => [
                    'TOTAL_BASE_CALCULATION' => 'TOTAL_BASE_CALCULATION',
                    'optionTemplate' => '<%= data.label %><% if (data.basePrice.value) '
                        . '{ %> +<%= data.basePrice.formatted %><% } %>',
                ],
                'weeeDisplay' =>  true,
                'weeeEnabled' =>  false,
                'expectedArray' => [
                    'TOTAL_BASE_CALCULATION' => 'TOTAL_BASE_CALCULATION',
                    'optionTemplate' => '<%= data.label %><% if (data.basePrice.value) '
                        . '{ %> +<%= data.basePrice.formatted %><% } %>',
                ],
            ],
            [
                'testArray' => [
                    'TOTAL_BASE_CALCULATION' => 'TOTAL_BASE_CALCULATION',
                    'optionTemplate' => '<%= data.label %><% if (data.basePrice.value) '
                        . '{ %> +<%= data.basePrice.formatted %><% } %>',
                ],
                'weeeDisplay' =>  false,
                'weeeEnabled' =>  true,
                'expectedArray' => [
                    'TOTAL_BASE_CALCULATION' => 'TOTAL_BASE_CALCULATION',
                    'optionTemplate' => '<%= data.label %><% if (data.basePrice.value) '
                        . '{ %> +<%= data.basePrice.formatted %><% } %> <% if (data.weeePricefpt1) '
                        . '{ %>  (:<%= data.weeePricefpt1.formatted %>)<% } %>'
                        . ' <% if (data.weeePricefpt2) { %>  (:<%= data.weeePricefpt2.formatted %>)<% } %>',
                ],
            ],
            [
                'testArray' => [
                    'TOTAL_BASE_CALCULATION' => 'TOTAL_BASE_CALCULATION',
                    'optionTemplate' => '<%= data.label %><% if (data.basePrice.value) '
                        . '{ %> +<%= data.basePrice.formatted %><% } %>',
                ],
                'weeeDisplay' =>  true,
                'weeeEnabled' =>  true,
                'expectedArray' => [
                    'TOTAL_BASE_CALCULATION' => 'TOTAL_BASE_CALCULATION',
                    'optionTemplate' => '<%= data.label %><% if (data.basePrice.value) '
                        . '{ %> +<%= data.basePrice.formatted %><% } %> <% if (data.weeePricefpt1) '
                        . '{ %>  (:<%= data.weeePricefpt1.formatted %>)<% } %> '
                        . '<% if (data.weeePricefpt2) { %>  (:<%= data.weeePricefpt2.formatted %>)<% } %> '
                        . '<% if (data.weeePrice) { %><%= data.weeePrice.formatted %><% } %>',
                ],
            ],
        ];
    }
}
