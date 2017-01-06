<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Test\Unit\Observer;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class GetPriceConfigurationObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests the methods that rely on the ScopeConfigInterface object to provide their return values
     * @dataProvider getPriceConfigurationProvider
     * @param bool $hasWeeeAttributes
     * @param array $testArray
     * @param array $expectedArray
     */
    public function testGetPriceConfiguration($hasWeeeAttributes, $testArray, $expectedArray)
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

        $weeeHelper=$this->getMock(\Magento\Weee\Helper\Data::class, [], [], '', false);
        $weeeHelper->expects($this->any())
            ->method('isEnabled')
            ->will($this->returnValue(true));

        $observerObject=$this->getMock(\Magento\Framework\Event\Observer::class, [], [], '', false);
        $observerObject->expects($this->any())
            ->method('getData')
            ->with('configObj')
            ->will($this->returnValue($configObj));

        $productInstance=$this->getMock(\Magento\Catalog\Model\Product\Type\Simple::class, [], [], '', false);

        $product=$this->getMock(
            \Magento\Bundle\Model\Product\Type::class,
            ['getTypeInstance', 'getTypeId', 'getStoreId'],
            [],
            '',
            false
        );
        $product->expects($this->any())
            ->method('getTypeInstance')
            ->will($this->returnValue($productInstance));
        $product->expects($this->any())
            ->method('getTypeId')
            ->will($this->returnValue('simple'));
        $product->expects($this->any())
            ->method('getStoreId')
            ->will($this->returnValue(null));

        $registry=$this->getMock(\Magento\Framework\Registry::class, [], [], '', false);
        $registry->expects($this->any())
            ->method('registry')
            ->with('current_product')
            ->will($this->returnValue($product));

        if ($hasWeeeAttributes) {
            $weeeHelper->expects($this->any())
                ->method('getWeeeAttributesForBundle')
                ->will($this->returnValue([
                    1 => ['fpt1' => $weeeObject1],
                    2 => [
                        'fpt1' => $weeeObject1,
                        'fpt2' => $weeeObject2
                    ]
                ]));
        } else {
            $weeeHelper->expects($this->any())
                ->method('getWeeeAttributesForBundle')
                ->will($this->returnValue(null));
        }

        $objectManager = new ObjectManager($this);
        /** @var \Magento\Weee\Observer\GetPriceConfigurationObserver $weeeObserverObject */
        $weeeObserverObject = $objectManager->getObject(
            \Magento\Weee\Observer\GetPriceConfigurationObserver::class,
            [
                'weeeData' => $weeeHelper,
                'registry' => $registry,
            ]
        );
        $weeeObserverObject->execute($observerObject);

        $this->assertEquals($expectedArray, $configObj->getData('config'));
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getPriceConfigurationProvider()
    {
        return [
            "basic" => [
                'hasWeeeAttributes' => true,
                'testArray' => [
                    [
                        [
                            'optionId' => 1,
                            'prices' =>
                                [
                                    'finalPrice' => ['amount' => 31.50],
                                    'basePrice' => ['amount' => 33.50],
                                ],
                        ],
                        [
                            'optionId' => 2,
                            'prices' =>
                                [
                                    'finalPrice' =>['amount' => 331.50],
                                    'basePrice' => ['amount' => 333.50],
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
                                    'finalPrice' => ['amount' => 31.50],
                                    'basePrice' => ['amount' => 33.50],
                                    'weeePrice' => ['amount' => 46.5],
                                    'weeePricefpt1' => ['amount' => 15],
                                ],
                        ],
                        [
                            'optionId' => 2,
                            'prices' =>
                                [
                                    'finalPrice' =>['amount' => 331.50],
                                    'basePrice' => ['amount' => 333.50],
                                    'weeePrice' => ['amount' => 362.5],
                                    'weeePricefpt1' => ['amount' => 15],
                                    'weeePricefpt2' => ['amount' => 16],
                                ],
                        ],
                    ],
                ],
            ],

            "layered, with extra keys" => [
                'hasWeeeAttributes' => true,
                'testArray' => [
                    [
                        [
                            'prices' =>
                                [
                                    'finalPrice' => ['amount' => 31.50],
                                ],
                            'somekey' => 0,
                        ],
                        [
                            [
                                [
                                    'prices' =>
                                        [
                                            'finalPrice' =>['amount' => 321.50],
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
                                    'finalPrice' => ['amount' => 31.50],
                                    'weeePrice' => ['amount' => 31.50],
                                ],
                            'somekey' => 0,
                        ],
                        [
                            [
                                [
                                    'prices' =>
                                        [
                                            'finalPrice' =>['amount' => 321.50],
                                            'weeePrice' => ['amount' => 321.50],
                                        ],
                                ],
                                'otherkey' => [ 1, 2 , 3],
                            ]
                        ],
                    ],
                ],
            ],

            "no Weee attributes, expect WeeePrice to be same as FinalPrice" => [
                'hasWeeeAttributes' => false,
                'testArray' => [
                    [
                        [
                            'optionId' => 1,
                            'prices' =>
                                [
                                    'basePrice' => ['amount' => 10],
                                    'finalPrice' => ['amount' => 11],
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
                                    'basePrice' => ['amount' => 10],
                                    'finalPrice' => ['amount' => 11],
                                    'weeePrice' => ['amount' => 11],
                                ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
