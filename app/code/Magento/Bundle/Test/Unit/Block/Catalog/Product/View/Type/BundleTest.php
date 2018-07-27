<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Bundle\Test\Unit\Block\Catalog\Product\View\Type;

use Magento\Bundle\Block\Catalog\Product\View\Type\Bundle as BundleBlock;
use Magento\Framework\DataObject as MagentoObject;

class BundleTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\Bundle\Model\Product\PriceFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $bundleProductPriceFactory;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectHelper;

    /**
     * @var \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle
     */
    protected $_bundleBlock;

    /** @var  \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject */
    private $product;

    protected function setUp()
    {
        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->bundleProductPriceFactory = $this->getMockBuilder('\Magento\Bundle\Model\Product\PriceFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->_bundleBlock = $objectHelper->getObject(
            'Magento\Bundle\Block\Catalog\Product\View\Type\Bundle',
            [
                'productPrice' => $this->bundleProductPriceFactory
            ]
        );
        $this->product = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getTypeInstance',
                    'getPriceInfo',
                    'getStoreId',
                    'getPriceType',
                    'hasPreconfiguredValues',
                    'getPreconfiguredValues'
                ]
            )->getMock();
    }

    public function testGetOptionHtmlNoRenderer()
    {
        $option = $this->getMock('\Magento\Bundle\Model\Option', ['getType', '__wakeup'], [], '', false);
        $option->expects($this->exactly(2))->method('getType')->will($this->returnValue('checkbox'));

        $this->assertEquals(
            'There is no defined renderer for "checkbox" option type.',
            $this->_bundleBlock->getOptionHtml($option)
        );
    }

    public function testGetOptionHtml()
    {
        $option = $this->getMock('\Magento\Bundle\Model\Option', ['getType', '__wakeup'], [], '', false);
        $option->expects($this->exactly(1))->method('getType')->will($this->returnValue('checkbox'));

        $optionBlock = $this->getMock(
            '\Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option\Checkbox',
            ['setOption', 'toHtml'],
            [],
            '',
            false
        );
        $optionBlock->expects($this->any())->method('setOption')->will($this->returnValue($optionBlock));
        $optionBlock->expects($this->any())->method('toHtml')->will($this->returnValue('option html'));
        $layout = $this->getMock(
            'Magento\Framework\View\Layout',
            ['getChildName', 'getBlock'],
            [],
            '',
            false
        );
        $layout->expects($this->any())->method('getChildName')->will($this->returnValue('name'));
        $layout->expects($this->any())->method('getBlock')->will($this->returnValue($optionBlock));
        $this->_bundleBlock->setLayout($layout);

        $this->assertEquals('option html', $this->_bundleBlock->getOptionHtml($option));
    }

    public function testGetJsonConfigFixedPriceBundleNoOption()
    {
        $options = [];
        $finalPriceMock = $this->getPriceMock(
            [
                'getPriceWithoutOption' => new MagentoObject(
                    [
                        'value' => 100,
                        'base_amount' => 100,
                    ]
                ),
            ]
        );
        $regularPriceMock = $this->getPriceMock(
            [
                'getAmount' => new MagentoObject(
                    [
                        'value' => 110,
                        'base_amount' => 110,
                    ]
                ),
            ]
        );
        $prices = [
            \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE => $finalPriceMock,
            \Magento\Catalog\Pricing\Price\RegularPrice::PRICE_CODE => $regularPriceMock,
        ];
        $priceInfo = $this->getPriceInfoMock($prices);

        $this->_bundleBlock = $this->setupBundleBlock(
            $options,
            $priceInfo,
            \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED
        );
        $jsonConfig = $this->_bundleBlock->getJsonConfig();
        $this->assertEquals(110, $jsonConfig['prices']['oldPrice']['amount']);
        $this->assertEquals(100, $jsonConfig['prices']['basePrice']['amount']);
        $this->assertEquals(100, $jsonConfig['prices']['finalPrice']['amount']);
    }

    public function testGetJsonConfigFixedPriceBundle()
    {
        $baseAmount = 123;
        $basePriceValue = 123123;
        $selections = [
            $this->createOptionSelection(
                1123,
                'Selection 1',
                23,
                [
                    ['price' => new MagentoObject(['base_amount' => $baseAmount, 'value' => $basePriceValue])]
                ],
                true,
                true
            )
        ];

        $bundleProductPrice = $this->getMockBuilder('\Magento\Bundle\Model\Product\Price')
            ->disableOriginalConstructor()
            ->setMethods(['getLowestPrice'])
            ->getMock();
        $bundleProductPrice->expects($this->at(0))
            ->method('getLowestPrice')
            ->with($this->product, $baseAmount)
            ->will($this->returnValue(999));
        $bundleProductPrice->expects($this->at(1))
            ->method('getLowestPrice')
            ->with($this->product, $basePriceValue)
            ->will($this->returnValue(888));
        $this->bundleProductPriceFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($bundleProductPrice));

        $options = [
            $this->createOption(1, 'Title `1', $selections),
        ];
        $finalPriceMock = $this->getPriceMock(
            [
                'getPriceWithoutOption' => new MagentoObject(
                    [
                        'value' => 100,
                        'base_amount' => 100,
                    ]
                ),
            ]
        );
        $regularPriceMock = $this->getPriceMock(
            [
                'getAmount' => new MagentoObject(
                    [
                        'value' => 110,
                        'base_amount' => 110,
                    ]
                ),
            ]
        );
        $prices = [
            'bundle_option' => $this->getAmountPriceMock(
                $baseAmount,
                $regularPriceMock,
                [['item' => $selections[0], 'value' => $basePriceValue, 'base_amount' => 321321]]
            ),
            \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE => $finalPriceMock,
            \Magento\Catalog\Pricing\Price\RegularPrice::PRICE_CODE => $regularPriceMock,
        ];
        $priceInfo = $this->getPriceInfoMock($prices);

        $this->product->expects($this->once())
            ->method('hasPreconfiguredValues')
            ->will($this->returnValue(true));
        $preconfiguredValues = new \Magento\Framework\DataObject(
            [
                'bundle_option' => [
                    1 => 123123111
                ]
            ]
        );
        $this->product->expects($this->once())
            ->method('getPreconfiguredValues')
            ->will($this->returnValue($preconfiguredValues));

        $this->_bundleBlock = $this->setupBundleBlock(
            $options,
            $priceInfo,
            \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED
        );
        $jsonConfig = $this->_bundleBlock->getJsonConfig();
        $this->assertEquals(110, $jsonConfig['prices']['oldPrice']['amount']);
        $this->assertEquals(100, $jsonConfig['prices']['basePrice']['amount']);
        $this->assertEquals(100, $jsonConfig['prices']['finalPrice']['amount']);
    }

    /**
     * @param array $options
     * @param \Magento\Framework\Pricing\PriceInfo\Base|\PHPUnit_Framework_MockObject_MockObject $priceInfo
     * @param string $priceType
     * @return BundleBlock
     */
    private function setupBundleBlock($options, $priceInfo, $priceType)
    {
        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);


        $eventManager = $this->getMockBuilder('\Magento\Framework\Event\Manager')
            ->disableOriginalConstructor()
            ->getMock();
        $eventManager->expects($this->any())->method('dispatch')->will($this->returnValue(true));

        $optionCollection = $this->getMockBuilder('\Magento\Bundle\Model\ResourceModel\Option\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $optionCollection->expects($this->any())
            ->method('appendSelections')
            ->will($this->returnValue($options));

        $typeInstance = $this->getMockBuilder('\Magento\Bundle\Model\Product\Type')
            ->disableOriginalConstructor()
            ->getMock();
        $typeInstance->expects($this->any())
            ->method('getOptionsCollection')
            ->will($this->returnValue($optionCollection));
        $typeInstance->expects($this->any())
            ->method('getStoreFilter')
            ->will($this->returnValue(true));

        $this->product->expects($this->any())
            ->method('getTypeInstance')
            ->will($this->returnValue($typeInstance));
        $this->product->expects($this->any())
            ->method('getPriceInfo')
            ->will($this->returnValue($priceInfo));
        $this->product->expects($this->any())
            ->method('getPriceType')
            ->will($this->returnValue($priceType));

        $registry = $this->getMockBuilder('\Magento\Framework\Registry')
            ->disableOriginalConstructor()
            ->setMethods(['registry'])
            ->getMock();
        $registry->expects($this->once())
            ->method('registry')
            ->will($this->returnValue($this->product));

        $taxHelperMock = $this->getMockBuilder('\Magento\Tax\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();

        $context = $this->getMockBuilder('\Magento\Catalog\Block\Product\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->any())
            ->method('getRegistry')
            ->will($this->returnValue($registry));
        $context->expects($this->any())
            ->method('getTaxData')
            ->will($this->returnValue($taxHelperMock));
        $context->expects($this->any())
            ->method('getEventManager')
            ->will($this->returnValue($eventManager));

        $jsonEncoderMock = $this->getMockBuilder('\Magento\Framework\Json\Encoder')
            ->disableOriginalConstructor()
            ->getMock();
        $jsonEncoderMock->expects($this->any())
            ->method('encode')
            ->will($this->returnArgument(0));

        /** @var $bundleBlock BundleBlock */
        $bundleBlock = $objectHelper->getObject(
            'Magento\Bundle\Block\Catalog\Product\View\Type\Bundle',
            [
                'context' => $context,
                'jsonEncoder' => $jsonEncoderMock,
                'productPrice' => $this->bundleProductPriceFactory
            ]
        );

        return $bundleBlock;
    }

    /**
     * @param $price
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getPriceInfoMock($price)
    {
        $priceInfoMock = $this->getMockBuilder('\Magento\Framework\Pricing\PriceInfo\Base')
            ->disableOriginalConstructor()
            ->setMethods(['getPrice'])
            ->getMock();

        if (is_array($price)) {
            $counter = 0;
            foreach ($price as $priceType => $priceValue) {
                $priceInfoMock->expects($this->at($counter))
                    ->method('getPrice')
                    ->with($priceType)
                    ->will($this->returnValue($priceValue));
                $counter++;
            }
        } else {
            $priceInfoMock->expects($this->any())
                ->method('getPrice')
                ->will($this->returnValue($price));
        }
        return $priceInfoMock;
    }

    /**
     * @param $prices
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getPriceMock($prices)
    {
        $methods = [];
        foreach (array_keys($prices) as $methodName) {
            $methods[] = $methodName;
        }
        $priceMock = $this->getMockBuilder('Magento\Catalog\Pricing\Price\BasePrice')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
        foreach ($prices as $methodName => $amount) {
            $priceMock->expects($this->any())
                ->method($methodName)
                ->will($this->returnValue($amount));
        }

        return $priceMock;
    }

    /**
     * @param float $value
     * @param mixed $baseAmount
     * @param array $selectionAmounts
     * @return \Magento\Framework\Pricing\Amount\AmountInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getAmountPriceMock($value, $baseAmount, array $selectionAmounts)
    {
        $amountPrice = $this->getMockBuilder('\Magento\Framework\Pricing\Amount\AmountInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getValue', 'getBaseAmount', 'getOptionSelectionAmount'])
            ->getMockForAbstractClass();
        $amountPrice->expects($this->any())->method('getValue')->will($this->returnValue($value));
        $amountPrice->expects($this->any())->method('getBaseAmount')->will($this->returnValue($baseAmount));
        foreach ($selectionAmounts as $selectionAmount) {
            $amountPrice->expects($this->any())
                ->method('getOptionSelectionAmount')
                ->with($selectionAmount['item'])
                ->will(
                    $this->returnValue(
                        new MagentoObject(
                            [
                                'value' => $selectionAmount['value'],
                                'base_amount' => $selectionAmount['base_amount'],
                            ]
                        )
                    )
                );
        }

        return $amountPrice;
    }

    /**
     * @param int $id
     * @param string $title
     * @param \Magento\Catalog\Model\Product[] $selections
     * @param int|string $type
     * @param bool $isRequired
     * @return \PHPUnit_Framework_MockObject_MockObject
     * @internal param bool $isDefault
     */
    private function createOption(
        $id,
        $title,
        array $selections = [],
        $type = 'checkbox',
        $isRequired = false
    ) {
        $option = $this->getMockBuilder('\Magento\Bundle\Model\Option')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    '__wakeup',
                    'getId',
                    'getTitle',
                    'getSelections',
                    'getType',
                    'getRequired',
                    'getIsDefault',
                ]
            )
            ->getMock();
        $option->expects($this->any())->method('getId')->will($this->returnValue($id));
        $option->expects($this->any())->method('getTitle')->will($this->returnValue($title));
        $option->expects($this->any())->method('getSelections')->will($this->returnValue($selections));
        $option->expects($this->any())->method('getType')->will($this->returnValue($type));
        $option->expects($this->any())->method('getRequired')->will($this->returnValue($isRequired));
        return $option;
    }

    /**
     * @param int $id
     * @param string $name
     * @param float $qty
     * @param array $tierPriceList
     * @param bool $isCanChangeQty
     * @param bool $isDefault
     * @param bool $isSalable
     * @return \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createOptionSelection(
        $id,
        $name,
        $qty,
        array $tierPriceList = [],
        $isCanChangeQty = true,
        $isDefault = false,
        $isSalable = true
    ) {
        $selection = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getSelectionId',
                    'getSelectionQty',
                    'getPriceInfo',
                    'getSelectionCanChangeQty',
                    'getName',
                    'getIsDefault',
                    'isSalable',
                ]
            )->getMock();
        $tierPrice = $this->getMockBuilder('\Magento\Bundle\Pricing\Price\TierPrice')
            ->disableOriginalConstructor()
            ->setMethods(['getTierPriceList'])
            ->getMock();
        $tierPrice->expects($this->any())
            ->method('getTierPriceList')
            ->will($this->returnValue($tierPriceList));
        $priceInfo = $this->getMockBuilder('\Magento\Framework\Pricing\PriceInfo\Base')
            ->disableOriginalConstructor()
            ->setMethods(['getPrice'])
            ->getMock();
        $priceInfo->expects($this->any())
            ->method('getPrice')
            ->will($this->returnValue($tierPrice));

        $selection->expects($this->any())->method('getSelectionId')->will($this->returnValue($id));
        $selection->expects($this->any())->method('getName')->will($this->returnValue($name));
        $selection->expects($this->any())->method('getSelectionQty')->will($this->returnValue($qty));
        $selection->expects($this->any())->method('getPriceInfo')->will($this->returnValue($priceInfo));
        $selection->expects($this->any())->method('getSelectionCanChangeQty')->will(
            $this->returnValue($isCanChangeQty)
        );
        $selection->expects($this->any())->method('getIsDefault')->will($this->returnValue($isDefault));
        $selection->expects($this->any())->method('isSalable')->will($this->returnValue($isSalable));

        return $selection;
    }
}
