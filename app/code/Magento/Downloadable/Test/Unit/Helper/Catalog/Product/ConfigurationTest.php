<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Unit\Helper\Catalog\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Downloadable\Helper\Catalog\Product\Configuration */
    protected $helper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Helper\Context
     */
    protected $context;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Helper\Product\Configuration
     */
    protected $productConfig;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->context = $this->getMockBuilder(\Magento\Framework\App\Helper\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfig = $this->getMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->productConfig = $this->getMockBuilder(\Magento\Catalog\Helper\Product\Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->once())->method('getScopeConfig')->willReturn($this->scopeConfig);
        $this->helper = $this->objectManagerHelper->getObject(
            \Magento\Downloadable\Helper\Catalog\Product\Configuration::class,
            [
                'context' => $this->context,
                'productConfig' => $this->productConfig
            ]
        );
    }

    public function testGetLinksTitle()
    {
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['_wakeup', 'getLinksTitle'])
            ->getMock();

        $product->expects($this->once())->method('getLinksTitle')->willReturn('links_title');

        $this->assertEquals('links_title', $this->helper->getLinksTitle($product));
    }

    public function testGetLinksTitleWithoutTitle()
    {
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['_wakeup', 'getLinksTitle'])
            ->getMock();

        $product->expects($this->once())->method('getLinksTitle')->willReturn(null);
        $this->scopeConfig->expects($this->once())->method('getValue')->with(
            \Magento\Downloadable\Model\Link::XML_PATH_LINKS_TITLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )->willReturn('scope_config_value');

        $this->assertEquals('scope_config_value', $this->helper->getLinksTitle($product));
    }

    public function testGetOptions()
    {
        $item = $this->getMock(\Magento\Catalog\Model\Product\Configuration\Item\ItemInterface::class);
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['_wakeup', 'getLinksTitle', 'getTypeInstance'])
            ->getMock();
        $option = $this->getMock(\Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface::class);
        $productType = $this->getMockBuilder(\Magento\Downloadable\Model\Product\Type::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLinks'])
            ->getMock();
        $productLink = $this->getMockBuilder(\Magento\Downloadable\Model\Link::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTitle'])
            ->getMock();

        $this->productConfig->expects($this->once())->method('getOptions')->with($item);
        $item->expects($this->any())->method('getProduct')->willReturn($product);
        $item->expects($this->once())->method('getOptionByCode')->willReturn($option);
        $product->expects($this->once())->method('getTypeInstance')->willReturn($productType);
        $productType->expects($this->once())->method('getLinks')->with($product)->willReturn([1 => $productLink]);
        $option->expects($this->once())->method('getValue')->willReturn(1);
        $product->expects($this->once())->method('getLinksTitle')->willReturn('links_title');
        $productLink->expects($this->once())->method('getTitle')->willReturn('title');

        $this->assertEquals([['label' => 'links_title', 'value' => ['title']]], $this->helper->getOptions($item));
    }
}
