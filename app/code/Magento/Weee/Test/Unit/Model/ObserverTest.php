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
     *
     */
    public function testGetPriceConfiguration()
    {
        $testArray=[
            [
                [
                    'prices' =>
                        [
                            'finalPrice' => [
                                    'amount' => 31.50,
                            ],
                        ],
                ],
                [
                    'prices' =>
                        [
                            'finalPrice' =>[
                                'amount' => 31.50,
                            ],
                        ],
                ],
            ],
        ];

        $configObj = new \Magento\Framework\Object(
            [
                'config' => $testArray,
            ]
        );

        $testArrayWithWeee=$testArray;
        $testArrayWithWeee[0][0]['prices']['weeePrice']= [
            'amount' => $testArray[0][0]['prices']['finalPrice']['amount'],
        ];
        $testArrayWithWeee[0][1]['prices']['weeePrice']= [
            'amount' => $testArray[0][1]['prices']['finalPrice']['amount'],
        ];

        $weeHelper=$this->getMock('Magento\Weee\Helper\Data', [], [], '', false);
        $weeHelper->expects($this->any())
            ->method('isEnabled')
            ->will($this->returnValue(true));

        $observerObject=$this->getMock('Magento\Framework\Event\Observer', [], [], '', false);

        $observerObject->expects($this->any())
            ->method('getData')
            ->with('configObj')
            ->will($this->returnValue($configObj));

         $objectManager = new ObjectManager($this);
         $weeeObserverObject = $objectManager->getObject(
             'Magento\Weee\Model\Observer',
             [
                 'weeeData' => $weeHelper,
             ]
         );
         $weeeObserverObject->getPriceConfiguration($observerObject);

         $this->assertEquals($testArrayWithWeee, $configObj->getData('config'));
    }
}
