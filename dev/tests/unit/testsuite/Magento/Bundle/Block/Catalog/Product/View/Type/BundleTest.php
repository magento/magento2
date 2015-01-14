<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Block\Catalog\Product\View\Type;

use Magento\Bundle\Block\Catalog\Product\View\Type\Bundle as BundleBlock;
use Magento\Framework\Object as MagentoObject;

class BundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectHelper;

    /**
     * @var \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle
     */
    protected $_bundleBlock;

    protected function setUp()
    {
        $objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_bundleBlock = $objectHelper->getObject('Magento\Bundle\Block\Catalog\Product\View\Type\Bundle');
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

    /**
     * @param array $options
     * @param \Magento\Framework\Pricing\PriceInfo\Base|\PHPUnit_Framework_MockObject_MockObject $priceInfo
     * @param string $priceType
     * @return Bundle
     */
    protected function setupBundleBlock($options, $priceInfo, $priceType)
    {
        $objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $optionCollection = $this->getMockBuilder('\Magento\Bundle\Model\Resource\Option\Collection')
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

        $product = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getTypeInstance',
                    'getPriceInfo',
                    'getStoreId',
                    'getPriceType',
                ]
            )
            ->getMock();
        $product->expects($this->any())
            ->method('getTypeInstance')
            ->will($this->returnValue($typeInstance));
        $product->expects($this->any())
            ->method('getPriceInfo')
            ->will($this->returnValue($priceInfo));
        $product->expects($this->any())
            ->method('getPriceType')
            ->will($this->returnValue($priceType));

        $registry = $this->getMockBuilder('\Magento\Framework\Registry')
            ->disableOriginalConstructor()
            ->setMethods(['registry'])
            ->getMock();
        $registry->expects($this->once())
            ->method('registry')
            ->will($this->returnValue($product));

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
                'jsonEncoder' => $jsonEncoderMock
            ]
        );

        return $bundleBlock;
    }

    public function getPriceInfoMock($price)
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

    public function getPriceMock($prices)
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
}
