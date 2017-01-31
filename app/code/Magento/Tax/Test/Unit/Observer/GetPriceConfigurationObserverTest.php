<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Observer;

use Magento\Tax\Observer\GetPriceConfigurationObserver;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class GetPriceConfigurationObserverTest
 */
class GetPriceConfigurationObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tax\Observer\GetPriceConfigurationObserver
     */
    protected $model;
    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var \Magento\Tax\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxData;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;


    /**
     * test Execute
     * @dataProvider getPriceConfigurationProvider
     * @param array $testArray
     * @param array $expectedArray
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecute($testArray, $expectedArray)
    {
        $configObj = new \Magento\Framework\DataObject(
            [
                'config' => $testArray,
            ]
        );

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $className = '\Magento\Framework\Registry';
        $this->registry = $this->getMock($className, [], [], '', false);

        $className = '\Magento\Tax\Helper\Data';
        $this->taxData = $this->getMock($className, [], [], '', false);

        $observerObject=$this->getMock('Magento\Framework\Event\Observer', [], [], '', false);
        $observerObject->expects($this->any())
            ->method('getData')
            ->with('configObj')
            ->will($this->returnValue($configObj));

        $baseAmount = $this->getMock(
            'Magento\Framework\Pricing\Amount\Base',
            ['getBaseAmount', 'getAdjustmentAmount', 'hasAdjustment'],
            [],
            '',
            false
        );

        $baseAmount->expects($this->any())
            ->method('hasAdjustment')
            ->will($this->returnValue(true));

        $baseAmount->expects($this->any())
            ->method('getBaseAmount')
            ->will($this->returnValue(33.5));

        $baseAmount->expects($this->any())
            ->method('getAdjustmentAmount')
            ->will($this->returnValue(1.5));

        $priceInfo = $this->getMock('Magento\Framework\Pricing\Priceinfo\Base', ['getPrice'], [], '', false);

        $basePrice = $this->getMock('Magento\Catalog\Price\BasePrice', ['getAmount'], [], '', false);

        $basePrice->expects($this->any())
            ->method('getAmount')
            ->will($this->returnValue($baseAmount));

        $priceInfo->expects($this->any())
            ->method('getPrice')
            ->will($this->returnValue($basePrice));

        $prod1 = $this->getMock('Magento\Catalog\Model\Product', ['getId', 'getPriceInfo'], [], '', false);
        $prod2 = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);

        $prod1->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));

        $prod1->expects($this->any())
            ->method('getPriceInfo')
            ->will($this->returnValue($priceInfo));

        $optionCollection =
            $this->getMock('Magento\Bundle\Model\ResourceModel\Selection\Collection', ['getItems'], [], '', false);

        $optionCollection->expects($this->any())
            ->method('getItems')
            ->will($this->returnValue([$prod1, $prod2]));

        $productInstance =
            $this->getMock(
                'Magento\Catalog\Model\Product\Type',
                ['setStoreFilter', 'getSelectionsCollection', 'getOptionsIds'],
                [],
                '',
                false
            );

        $product=$this->getMock(
            '\Magento\Bundle\Model\Product\Type',
            ['getTypeInstance', 'getTypeId', 'getStoreId', 'getSelectionsCollection'],
            [],
            '',
            false
        );
        $product->expects($this->any())
            ->method('getTypeInstance')
            ->will($this->returnValue($productInstance));
        $product->expects($this->any())
            ->method('getTypeId')
            ->will($this->returnValue('bundle'));
        $product->expects($this->any())
            ->method('getStoreId')
            ->will($this->returnValue(null));

        $productInstance->expects($this->any())
            ->method('getSelectionsCollection')
            ->will($this->returnValue($optionCollection));

        $productInstance->expects($this->any())
            ->method('getOptionsIds')
            ->will($this->returnValue(true));

        $this->registry->expects($this->any())
            ->method('registry')
            ->with('current_product')
            ->will($this->returnValue($product));


        $this->taxData->expects($this->any())
            ->method('displayPriceIncludingTax')
            ->will($this->returnValue(true));

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            'Magento\Tax\Observer\GetPriceConfigurationObserver',
            [
                'taxData' => $this->taxData,
                'registry' => $this->registry,
            ]
        );

        $this->model->execute($observerObject);

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
                'testArray' => [
                    [
                        [
                            'optionId' => 1,
                            'prices' =>
                                [
                                    'finalPrice' => ['amount' => 35.50],
                                    'basePrice' => ['amount' => 30.50],
                                ],
                        ],
                        [
                            'optionId' => 2,
                            'prices' =>
                                [
                                    'finalPrice' =>['amount' => 333.50],
                                    'basePrice' => ['amount' => 300.50],
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
                                    'finalPrice' => ['amount' => 35.50],
                                    'basePrice' => ['amount' => 35],
                                    'oldPrice' => ['amount' => 35],
                                ],
                        ],
                        [
                            'optionId' => 2,
                            'prices' =>
                                [
                                    'finalPrice' =>['amount' => 333.50],
                                    'basePrice' => ['amount' => 300.50],
                                ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
