<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Weee\Model\Observer
 */
namespace Magento\Weee\Test\Unit\Model;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests the methods that rely on the ScopeConfigInterface object to provide their return values
     * @dataProvider getPriceConfigurationProvider
     * @param array $testArray
     * @param array $expectedArray
     */
    public function testGetPriceConfiguration($testArray, $expectedArray)
    {
        $configObj = new \Magento\Framework\DataObject(
            [
                'config' => $testArray,
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
                'amount' => '16.0000',
            ]
        );

        $weeeHelper=$this->getMock('Magento\Weee\Helper\Data', [], [], '', false);
        $weeeHelper->expects($this->any())
            ->method('isEnabled')
            ->will($this->returnValue(true));

        $observerObject=$this->getMock('Magento\Framework\Event\Observer', [], [], '', false);
        $observerObject->expects($this->any())
            ->method('getData')
            ->with('configObj')
            ->will($this->returnValue($configObj));

        $productInstance=$this->getMock('\Magento\Catalog\Model\Product\Type\Simple', [], [], '', false);

        $product=$this->getMock('\Magento\Bundle\Model\Product\Type', ['getTypeInstance', 'getTypeId'], [], '', false);
        $product->expects($this->any())
            ->method('getTypeInstance')
            ->will($this->returnValue($productInstance));

        $product->expects($this->any())
            ->method('getTypeId')
            ->will($this->returnValue('simple'));

        $registry=$this->getMock('Magento\Framework\Registry', [], [], '', false);
        $registry->expects($this->any())
            ->method('registry')
            ->with('current_product')
            ->will($this->returnValue($product));

        $weeeHelper->expects($this->any())
            ->method('getWeeeAttributesForBundle')
            ->will($this->returnValue([1=> ['fpt1' => $weeeObject1], 2 =>['fpt1'=>$weeeObject1, 'fpt2'=> $weeeObject2]]));

        $objectManager = new ObjectManager($this);
        $weeeObserverObject = $objectManager->getObject(
            'Magento\Weee\Model\Observer',
            [
                'weeeData' => $weeeHelper,
                'registry' => $registry,
            ]
        );
        $weeeObserverObject->getPriceConfiguration($observerObject);

        $this->assertEquals($expectedArray, $configObj->getData('config'));
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getPriceConfigurationProvider()
    {
        return [
            [
                'testArray' => [
                    [
                        [
                            'optionId' => 1,
                            'prices' =>
                                [
                                    'finalPrice' => [
                                        'amount' => 31.50,
                                    ],
                                    'basePrice' => [
                                        'amount' => 33.50,
                                    ],
                                ],
                        ],
                        [
                            'optionId' => 2,
                            'prices' =>
                                [
                                    'finalPrice' =>[
                                        'amount' => 331.50,
                                    ],
                                    'basePrice' => [
                                        'amount' => 333.50,
                                    ],
                                ],
                        ],
                    ],
                ],
                'expectedArray' => [
                    [
                        [
                            'optionId' => 1,
                            'prices' =>
                                [
                                    'finalPrice' => [
                                        'amount' => 31.50,
                                    ],
                                    'basePrice' => [
                                        'amount' => 33.50,
                                    ],
                                    'weeePrice' => [
                                        'amount' => 46.5,
                                    ],
                                    'weeePricefpt1' => [
                                        'amount' => 15,
                                    ],
                                ],
                        ],
                        [
                            'optionId' => 2,
                            'prices' =>
                                [
                                    'finalPrice' =>[
                                        'amount' => 331.50,
                                    ],
                                    'basePrice' => [
                                        'amount' => 333.50,
                                    ],
                                    'weeePrice' => [
                                        'amount' => 362.5,
                                    ],
                                    'weeePricefpt1' => [
                                        'amount' => 15,
                                    ],
                                    'weeePricefpt2' => [
                                        'amount' => 16,
                                    ],
                                ],
                        ],
                    ],
                ],
            ],
            [
                'testArray' => [
                    [
                        [
                            'prices' =>
                                [
                                    'finalPrice' => [
                                        'amount' => 31.50,
                                    ],
                                ],
                                'somekey' => 0,
                        ],
                        [
                            [
                                [
                                    'prices' =>
                                        [
                                            'finalPrice' =>[
                                                'amount' => 31.50,
                                            ],
                                        ],
                                ],
                                'otherkey' => [ 1, 2 , 3],
                            ]
                        ],
                    ],
                ],
                'expectedArray' => [
                    [
                        [
                            'prices' =>
                                [
                                    'finalPrice' => [
                                        'amount' => 31.50,
                                    ],
                                    'weeePrice' => [
                                        'amount' => 31.50,
                                    ],
                                ],
                                'somekey' => 0,
                        ],
                        [
                            [
                                [
                                    'prices' =>
                                        [
                                            'finalPrice' =>[
                                                'amount' => 31.50,
                                            ],
                                            'weeePrice' => [
                                                'amount' => 31.50,
                                            ],
                                        ],
                                ],
                                'otherkey' => [ 1, 2 , 3],
                            ]
                        ],
                    ],
                ],
            ],
        ];
    }

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
        $weeeObserverObject = $objectManager->getObject(
            'Magento\Weee\Model\Observer',
            [
                'weeeData' => $weeeHelper,
                'registry' => $registry,
            ]
        );
        $weeeObserverObject->updateProductOptions($observerObject);

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
