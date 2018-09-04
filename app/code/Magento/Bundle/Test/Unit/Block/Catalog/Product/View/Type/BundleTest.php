<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Block\Catalog\Product\View\Type;

use Magento\Bundle\Block\Catalog\Product\View\Type\Bundle as BundleBlock;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BundleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Bundle\Model\Product\PriceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $bundleProductPriceFactory;

    /**
     * @var \Magento\Framework\Json\Encoder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $jsonEncoder;

    /**
     * @var \Magento\Catalog\Helper\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $catalogProduct;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventManager;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $product;

    /**
     * @var \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle
     */
    private $bundleBlock;

    protected function setUp()
    {
        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->bundleProductPriceFactory = $this->getMockBuilder(\Magento\Bundle\Model\Product\PriceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
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
        $registry = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->setMethods(['registry'])
            ->getMock();
        $registry->expects($this->any())
            ->method('registry')
            ->willReturn($this->product);
        $this->eventManager = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->jsonEncoder = $this->getMockBuilder(\Magento\Framework\Json\Encoder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->catalogProduct = $this->getMockBuilder(\Magento\Catalog\Helper\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var $bundleBlock BundleBlock */
        $this->bundleBlock = $objectHelper->getObject(
            \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle::class,
            [
                'registry' => $registry,
                'eventManager' => $this->eventManager,
                'jsonEncoder' => $this->jsonEncoder,
                'productPrice' => $this->bundleProductPriceFactory,
                'catalogProduct' => $this->catalogProduct
            ]
        );

        $ruleProcessor = $this->getMockBuilder(
            \Magento\CatalogRule\Model\ResourceModel\Product\CollectionProcessor::class
        )->disableOriginalConstructor()->getMock();
        $objectHelper->setBackwardCompatibleProperty(
            $this->bundleBlock,
            'catalogRuleProcessor',
            $ruleProcessor
        );
    }

    public function testGetOptionHtmlNoRenderer()
    {
        $option = $this->getMockBuilder(\Magento\Bundle\Model\Option::class)
            ->setMethods(['getType'])
            ->disableOriginalConstructor()
            ->getMock();
        $option->expects($this->any())->method('getType')->willReturn('checkbox');

        $layout = $this->getMockBuilder(\Magento\Framework\View\Layout::class)
            ->setMethods(['getChildName', 'getBlock'])
            ->disableOriginalConstructor()
            ->getMock();
        $layout->expects($this->any())->method('getChildName')->willReturn(false);
        $this->bundleBlock->setLayout($layout);

        $this->assertEquals(
            'There is no defined renderer for "checkbox" option type.',
            $this->bundleBlock->getOptionHtml($option)
        );
    }

    public function testGetOptionHtml()
    {
        $option = $this->getMockBuilder(\Magento\Bundle\Model\Option::class)
            ->setMethods(['getType'])
            ->disableOriginalConstructor()
            ->getMock();
        $option->expects($this->once())->method('getType')->willReturn('checkbox');

        $optionBlock = $this->getMockBuilder(
            \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option\Checkbox::class
        )->setMethods(['setOption', 'toHtml'])->disableOriginalConstructor()->getMock();
        $optionBlock->expects($this->any())->method('setOption')->willReturnSelf();
        $optionBlock->expects($this->any())->method('toHtml')->willReturn('option html');
        $layout = $this->getMockBuilder(\Magento\Framework\View\Layout::class)
            ->setMethods(['getChildName', 'getBlock'])
            ->disableOriginalConstructor()
            ->getMock();
        $layout->expects($this->any())->method('getChildName')->willReturn('name');
        $layout->expects($this->any())->method('getBlock')->willReturn($optionBlock);
        $this->bundleBlock->setLayout($layout);

        $this->assertEquals('option html', $this->bundleBlock->getOptionHtml($option));
    }

    public function testGetJsonConfigFixedPriceBundleNoOption()
    {
        $options = [];
        $finalPriceMock = $this->getPriceMock(
            [
                'getPriceWithoutOption' => new \Magento\Framework\DataObject(
                    [
                        'value' => 100,
                        'base_amount' => 100,
                    ]
                ),
            ]
        );
        $regularPriceMock = $this->getPriceMock(
            [
                'getAmount' => new \Magento\Framework\DataObject(
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

        $this->updateBundleBlock(
            $options,
            $priceInfo,
            \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED
        );
        $jsonConfig = $this->bundleBlock->getJsonConfig();
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
                    ['price' => new \Magento\Framework\DataObject(
                        ['base_amount' => $baseAmount, 'value' => $basePriceValue]
                    )],
                ],
                true,
                true
            )
        ];

        $bundleProductPrice = $this->getMockBuilder(\Magento\Bundle\Model\Product\Price::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLowestPrice'])
            ->getMock();
        $bundleProductPrice->expects($this->at(0))
            ->method('getLowestPrice')
            ->with($this->product, $baseAmount)
            ->willReturn(999);
        $bundleProductPrice->expects($this->at(1))
            ->method('getLowestPrice')
            ->with($this->product, $basePriceValue)
            ->willReturn(888);
        $this->bundleProductPriceFactory->expects($this->once())
            ->method('create')
            ->willReturn($bundleProductPrice);

        $options = [
            $this->createOption(1, 'Title `1', $selections),
        ];
        $finalPriceMock = $this->getPriceMock(
            [
                'getPriceWithoutOption' => new \Magento\Framework\DataObject(
                    [
                        'value' => 100,
                        'base_amount' => 100,
                    ]
                ),
            ]
        );
        $regularPriceMock = $this->getPriceMock(
            [
                'getAmount' => new \Magento\Framework\DataObject(
                    [
                        'value' => 110,
                        'base_amount' => 110,
                    ]
                ),
            ]
        );
        $bundleOptionPriceMock = $this->getAmountPriceMock(
            $baseAmount,
            $regularPriceMock,
            [['item' => $selections[0], 'value' => $basePriceValue, 'base_amount' => 321321]]
        );
        $prices = [
            'bundle_option' => $bundleOptionPriceMock,
            'bundle_option_regular_price' => $bundleOptionPriceMock,
            \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE => $finalPriceMock,
            \Magento\Catalog\Pricing\Price\RegularPrice::PRICE_CODE => $regularPriceMock,
        ];
        $priceInfo = $this->getPriceInfoMock($prices);

        $this->product->expects($this->once())
            ->method('hasPreconfiguredValues')
            ->willReturn(true);
        $preconfiguredValues = new \Magento\Framework\DataObject(
            [
                'bundle_option' => [
                    1 => 123123111,
                ],
            ]
        );
        $this->product->expects($this->once())
            ->method('getPreconfiguredValues')
            ->willReturn($preconfiguredValues);

        $this->updateBundleBlock(
            $options,
            $priceInfo,
            \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED
        );
        $jsonConfig = $this->bundleBlock->getJsonConfig();
        $this->assertEquals(110, $jsonConfig['prices']['oldPrice']['amount']);
        $this->assertEquals(100, $jsonConfig['prices']['basePrice']['amount']);
        $this->assertEquals(100, $jsonConfig['prices']['finalPrice']['amount']);
    }

    /**
     * @param array $options
     * @param \Magento\Framework\Pricing\PriceInfo\Base|\PHPUnit_Framework_MockObject_MockObject $priceInfo
     * @param string $priceType
     * @return void
     */
    private function updateBundleBlock($options, $priceInfo, $priceType)
    {
        $this->eventManager->expects($this->any())->method('dispatch')->willReturn(true);
        $optionCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Option\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $optionCollection->expects($this->any())
            ->method('appendSelections')
            ->willReturn($options);

        $selectionCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Selection\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $selectionCollection->expects($this->once())->method('addTierPriceData');

        $typeInstance = $this->getMockBuilder(\Magento\Bundle\Model\Product\Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $typeInstance->expects($this->any())
            ->method('getOptionsCollection')
            ->willReturn($optionCollection);
        $typeInstance->expects($this->any())
            ->method('getStoreFilter')
            ->willReturn(true);
        $typeInstance->expects($this->once())
            ->method('getSelectionsCollection')
            ->willReturn($selectionCollection);

        $this->product->expects($this->any())
            ->method('getTypeInstance')
            ->willReturn($typeInstance);
        $this->product->expects($this->any())
            ->method('getPriceInfo')
            ->willReturn($priceInfo);
        $this->product->expects($this->any())
            ->method('getPriceType')
            ->willReturn($priceType);
        $this->jsonEncoder->expects($this->any())
            ->method('encode')
            ->will($this->returnArgument(0));
    }

    /**
     * @param $price
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getPriceInfoMock($price)
    {
        $priceInfoMock = $this->getMockBuilder(\Magento\Framework\Pricing\PriceInfo\Base::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPrice'])
            ->getMock();

        if (is_array($price)) {
            $counter = 0;
            foreach ($price as $priceType => $priceValue) {
                $priceInfoMock->expects($this->at($counter))
                    ->method('getPrice')
                    ->with($priceType)
                    ->willReturn($priceValue);
                $counter++;
            }
        } else {
            $priceInfoMock->expects($this->any())
                ->method('getPrice')
                ->willReturn($price);
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
        $priceMock = $this->getMockBuilder(\Magento\Catalog\Pricing\Price\BasePrice::class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
        foreach ($prices as $methodName => $amount) {
            $priceMock->expects($this->any())
                ->method($methodName)
                ->willReturn($amount);
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
        $amountPrice = $this->getMockBuilder(\Magento\Framework\Pricing\Amount\AmountInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue', 'getBaseAmount', 'getOptionSelectionAmount'])
            ->getMockForAbstractClass();
        $amountPrice->expects($this->any())->method('getValue')->willReturn($value);
        $amountPrice->expects($this->any())->method('getBaseAmount')->willReturn($baseAmount);
        foreach ($selectionAmounts as $selectionAmount) {
            $amountPrice->expects($this->any())
                ->method('getOptionSelectionAmount')
                ->with($selectionAmount['item'])
                ->will(
                    $this->returnValue(
                        new \Magento\Framework\DataObject(
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
        $option = $this->getMockBuilder(\Magento\Bundle\Model\Option::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getId',
                    'getTitle',
                    'getSelections',
                    'getType',
                    'getRequired',
                    'getIsDefault',
                ]
            )
            ->getMockForAbstractClass();
        $option->expects($this->any())->method('getId')->willReturn($id);
        $option->expects($this->any())->method('getTitle')->willReturn($title);
        $option->expects($this->any())->method('getSelections')->willReturn($selections);
        $option->expects($this->any())->method('getType')->willReturn($type);
        $option->expects($this->any())->method('getRequired')->willReturn($isRequired);
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
        $selection = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(
                [
                    'getSelectionId',
                    'getName',
                    'getSelectionQty',
                    'getPriceInfo',
                    'getSelectionCanChangeQty',
                    'getIsDefault',
                    'isSalable'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $tierPrice = $this->getMockBuilder(\Magento\Bundle\Pricing\Price\TierPrice::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTierPriceList'])
            ->getMock();
        $tierPrice->expects($this->any())->method('getTierPriceList')->willReturn($tierPriceList);
        $priceInfo = $this->getMockBuilder(\Magento\Framework\Pricing\PriceInfo\Base::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPrice'])
            ->getMock();
        $priceInfo->expects($this->any())->method('getPrice')->willReturn($tierPrice);
        $selection->expects($this->any())->method('getSelectionId')->willReturn($id);
        $selection->expects($this->any())->method('getName')->willReturn($name);
        $selection->expects($this->any())->method('getSelectionQty')->willReturn($qty);
        $selection->expects($this->any())->method('getPriceInfo')->willReturn($priceInfo);
        $selection->expects($this->any())->method('getSelectionCanChangeQty')->willReturn($isCanChangeQty);
        $selection->expects($this->any())->method('getIsDefault')->willReturn($isDefault);
        $selection->expects($this->any())->method('isSalable')->willReturn($isSalable);

        return $selection;
    }

    /**
     * @dataProvider getOptionsDataProvider
     * @param bool $stripSelection
     */
    public function testGetOptions($stripSelection)
    {
        $newOptions = ['option_1', 'option_2'];

        $optionCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Option\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $selectionConnection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Selection\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $typeInstance = $this->getMockBuilder(\Magento\Bundle\Model\Product\Type::class)
            ->disableOriginalConstructor()
            ->getMock();

        $optionCollection->expects($this->any())->method('appendSelections')
            ->with($selectionConnection, $stripSelection, true)
            ->willReturn($newOptions);
        $typeInstance->expects($this->any())->method('setStoreFilter')->with(0, $this->product)
            ->willReturn($optionCollection);
        $typeInstance->expects($this->any())->method('getStoreFilter')->willReturn(true);
        $typeInstance->expects($this->any())->method('getOptionsCollection')->willReturn($optionCollection);
        $typeInstance->expects($this->any())->method('getOptionsIds')->willReturn([1, 2]);
        $typeInstance->expects($this->once())->method('getSelectionsCollection')->with([1, 2], $this->product)
            ->willReturn($selectionConnection);
        $this->product->expects($this->any())
            ->method('getTypeInstance')->willReturn($typeInstance);
        $this->product->expects($this->any())->method('getStoreId')->willReturn(0);
        $this->catalogProduct->expects($this->once())->method('getSkipSaleableCheck')->willReturn(true);

        $this->assertEquals($newOptions, $this->bundleBlock->getOptions($stripSelection));
    }

    /**
     * @return array
     */
    public function getOptionsDataProvider()
    {
        return [
            [true],
            [false]
        ];
    }
}
