<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Block\Catalog\Product\View\Type;

use Magento\Bundle\Block\Catalog\Product\View\Type\Bundle as BundleBlock;
use Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option\Checkbox;
use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Model\Product\PriceFactory;
use Magento\Bundle\Model\Product\Type;
use Magento\Bundle\Model\ResourceModel\Option\Collection;
use Magento\Bundle\Pricing\Price\TierPrice;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Pricing\Price\BasePrice;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\CatalogRule\Model\ResourceModel\Product\CollectionProcessor;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Json\Encoder;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BundleTest extends TestCase
{
    /**
     * @var PriceFactory|MockObject
     */
    private $bundleProductPriceFactory;

    /**
     * @var Encoder|MockObject
     */
    private $jsonEncoder;

    /**
     * @var Product|MockObject
     */
    private $catalogProduct;

    /**
     * @var ManagerInterface|MockObject
     */
    private $eventManager;

    /**
     * @var \Magento\Catalog\Model\Product|MockObject
     */
    private $product;

    /**
     * @var \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle
     */
    private $bundleBlock;

    /**
     * @var Escaper|MockObject
     */
    private $escaperMock;

    protected function setUp(): void
    {
        $objectHelper = new ObjectManager($this);

        $this->bundleProductPriceFactory = $this->getMockBuilder(PriceFactory::class)
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
        $registry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->setMethods(['registry'])
            ->getMock();
        $registry->expects($this->any())
            ->method('registry')
            ->willReturn($this->product);
        $this->eventManager = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->jsonEncoder = $this->getMockBuilder(Encoder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->catalogProduct = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->escaperMock = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var BundleBlock $bundleBlock */
        $this->bundleBlock = $objectHelper->getObject(
            \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle::class,
            [
                'registry' => $registry,
                'eventManager' => $this->eventManager,
                'jsonEncoder' => $this->jsonEncoder,
                'productPrice' => $this->bundleProductPriceFactory,
                'catalogProduct' => $this->catalogProduct,
                'escaper' => $this->escaperMock,
            ]
        );

        $ruleProcessor = $this->getMockBuilder(
            CollectionProcessor::class
        )->disableOriginalConstructor()
            ->getMock();
        $objectHelper->setBackwardCompatibleProperty(
            $this->bundleBlock,
            'catalogRuleProcessor',
            $ruleProcessor
        );
    }

    public function testGetOptionHtmlNoRenderer()
    {
        $option = $this->getMockBuilder(Option::class)
            ->setMethods(['getType'])
            ->disableOriginalConstructor()
            ->getMock();
        $option->expects($this->any())->method('getType')->willReturn('checkbox');
        $this->escaperMock->expects($this->once())->method('escapeHtml')->willReturn('checkbox');
        $expected='There is no defined renderer for "checkbox" option type.';
        $layout = $this->getMockBuilder(Layout::class)
            ->setMethods(['getChildName', 'getBlock'])
            ->disableOriginalConstructor()
            ->getMock();
        $layout->expects($this->any())->method('getChildName')->willReturn(false);
        $this->bundleBlock->setLayout($layout);
        $this->assertEquals(
            $expected,
            $this->bundleBlock->getOptionHtml($option)
        );
    }

    public function testGetOptionHtml()
    {
        $option = $this->getMockBuilder(Option::class)
            ->setMethods(['getType'])
            ->disableOriginalConstructor()
            ->getMock();
        $option->expects($this->once())->method('getType')->willReturn('checkbox');

        $optionBlock = $this->getMockBuilder(
            Checkbox::class
        )->setMethods(['setOption', 'toHtml'])->disableOriginalConstructor()
            ->getMock();
        $optionBlock->expects($this->any())->method('setOption')->willReturnSelf();
        $optionBlock->expects($this->any())->method('toHtml')->willReturn('option html');
        $layout = $this->getMockBuilder(Layout::class)
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
                'getPriceWithoutOption' => new DataObject(
                    [
                        'value' => 100,
                        'base_amount' => 100,
                    ]
                ),
            ]
        );
        $regularPriceMock = $this->getPriceMock(
            [
                'getAmount' => new DataObject(
                    [
                        'value' => 110,
                        'base_amount' => 110,
                    ]
                ),
            ]
        );
        $prices = [
            FinalPrice::PRICE_CODE => $finalPriceMock,
            RegularPrice::PRICE_CODE => $regularPriceMock,
        ];
        $priceInfo = $this->getPriceInfoMock($prices);

        $this->updateBundleBlock(
            $options,
            $priceInfo,
            Price::PRICE_TYPE_FIXED
        );
        $jsonConfig = $this->bundleBlock->getJsonConfig();
        $this->assertEquals(110, $jsonConfig['prices']['oldPrice']['amount']);
        $this->assertEquals(100, $jsonConfig['prices']['basePrice']['amount']);
        $this->assertEquals(100, $jsonConfig['prices']['finalPrice']['amount']);
    }

    public function testGetJsonConfigFixedPriceBundle()
    {
        $optionId = 1;
        $optionQty = 2;
        $baseAmount = 123;
        $basePriceValue = 123123;
        $selections = [
            $this->createOptionSelection(
                1123,
                'Selection 1',
                23,
                [
                    ['price' => new DataObject(
                        ['base_amount' => $baseAmount, 'value' => $basePriceValue]
                    )],
                ],
                true,
                true
            )
        ];
        $bundleProductPrice = $this->getMockBuilder(Price::class)
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
        $options = [$this->createOption($optionId, 'Title `1', $selections)];

        $finalPriceMock = $this->getPriceMock(
            [
                'getPriceWithoutOption' => new DataObject(
                    [
                        'value' => 100,
                        'base_amount' => 100,
                    ]
                ),
            ]
        );
        $regularPriceMock = $this->getPriceMock(
            [
                'getAmount' => new DataObject(
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
            FinalPrice::PRICE_CODE => $finalPriceMock,
            RegularPrice::PRICE_CODE => $regularPriceMock,
        ];
        $priceInfo = $this->getPriceInfoMock($prices);

        $this->product->expects($this->once())
            ->method('hasPreconfiguredValues')
            ->willReturn(true);
        $preconfiguredValues = new DataObject(
            [
                'bundle_option' => [
                    $optionId => [123123111],
                ],
                'bundle_option_qty' => [
                    $optionId => $optionQty,
                ],
            ]
        );
        $this->product->expects($this->once())
            ->method('getPreconfiguredValues')
            ->willReturn($preconfiguredValues);

        $this->updateBundleBlock(
            $options,
            $priceInfo,
            Price::PRICE_TYPE_FIXED
        );
        $jsonConfig = $this->bundleBlock->getJsonConfig();
        $this->assertEquals(110, $jsonConfig['prices']['oldPrice']['amount']);
        $this->assertEquals(100, $jsonConfig['prices']['basePrice']['amount']);
        $this->assertEquals(100, $jsonConfig['prices']['finalPrice']['amount']);
        $this->assertEquals([$optionId], $jsonConfig['positions']);
        $this->assertEquals($optionQty, $jsonConfig['options'][$optionId]['selections'][1123]['qty']);
    }

    /**
     * @param array $options
     * @param Base|MockObject $priceInfo
     * @param string $priceType
     * @return void
     */
    private function updateBundleBlock($options, $priceInfo, $priceType)
    {
        $this->eventManager->expects($this->any())->method('dispatch')->willReturn(true);
        $optionCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $optionCollection->expects($this->any())
            ->method('appendSelections')
            ->willReturn($options);

        $selectionCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Selection\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $selectionCollection->expects($this->once())->method('addTierPriceData');

        $typeInstance = $this->getMockBuilder(Type::class)
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
            ->willReturnArgument(0);
    }

    /**
     * @param $price
     * @return MockObject
     */
    private function getPriceInfoMock($price)
    {
        $priceInfoMock = $this->getMockBuilder(Base::class)
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
     * @return MockObject
     */
    private function getPriceMock($prices)
    {
        $methods = [];
        foreach (array_keys($prices) as $methodName) {
            $methods[] = $methodName;
        }
        $priceMock = $this->getMockBuilder(BasePrice::class)
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
     * @return AmountInterface|MockObject
     */
    private function getAmountPriceMock($value, $baseAmount, array $selectionAmounts)
    {
        $amountPrice = $this->getMockBuilder(AmountInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue', 'getBaseAmount', 'getOptionSelectionAmount'])
            ->getMockForAbstractClass();
        $amountPrice->expects($this->any())->method('getValue')->willReturn($value);
        $amountPrice->expects($this->any())->method('getBaseAmount')->willReturn($baseAmount);
        foreach ($selectionAmounts as $selectionAmount) {
            $amountPrice->expects($this->any())
                ->method('getOptionSelectionAmount')
                ->with($selectionAmount['item'])
                ->willReturn(
                    new DataObject(
                        [
                            'value' => $selectionAmount['value'],
                            'base_amount' => $selectionAmount['base_amount'],
                        ]
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
     * @return MockObject
     * @internal param bool $isDefault
     */
    private function createOption(
        $id,
        $title,
        array $selections = [],
        $type = 'checkbox',
        $isRequired = false
    ) {
        $option = $this->getMockBuilder(Option::class)
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
     * @return \Magento\Catalog\Model\Product|MockObject
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
        $tierPrice = $this->getMockBuilder(TierPrice::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTierPriceList'])
            ->getMock();
        $tierPrice->expects($this->any())->method('getTierPriceList')->willReturn($tierPriceList);
        $priceInfo = $this->getMockBuilder(Base::class)
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

        $optionCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $selectionConnection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Selection\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $typeInstance = $this->getMockBuilder(Type::class)
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
