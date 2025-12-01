<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Helper\Catalog\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface;
use Magento\Downloadable\Helper\Catalog\Product\Configuration;
use Magento\Downloadable\Model\Link;
use Magento\Downloadable\Model\Product\Type;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurationTest extends TestCase
{
    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var Configuration */
    protected $helper;

    /**
     * @var MockObject|Context
     */
    protected $context;

    /**
     * @var MockObject|ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var MockObject|\Magento\Catalog\Helper\Product\Configuration
     */
    protected $productConfig;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->context = $this->createMock(Context::class);
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->productConfig = $this->createMock(\Magento\Catalog\Helper\Product\Configuration::class);
        $this->context->expects($this->once())->method('getScopeConfig')->willReturn($this->scopeConfig);
        $this->helper = $this->objectManagerHelper->getObject(
            Configuration::class,
            [
                'context' => $this->context,
                'productConfig' => $this->productConfig
            ]
        );
    }

    public function testGetLinksTitle()
    {
        $product = $this->createPartialMock(
            \Magento\Catalog\Test\Unit\Helper\ProductTestHelper::class,
            ['__wakeup', 'getLinksTitle']
        );

        $product->expects($this->once())->method('getLinksTitle')->willReturn('links_title');

        $this->assertEquals('links_title', $this->helper->getLinksTitle($product));
    }

    public function testGetLinksTitleWithoutTitle()
    {
        $product = $this->createPartialMock(
            \Magento\Catalog\Test\Unit\Helper\ProductTestHelper::class,
            ['__wakeup', 'getLinksTitle']
        );

        $product->expects($this->once())->method('getLinksTitle')->willReturn(null);
        $this->scopeConfig->expects($this->once())->method('getValue')->with(
            Link::XML_PATH_LINKS_TITLE,
            ScopeInterface::SCOPE_STORE
        )->willReturn('scope_config_value');

        $this->assertEquals('scope_config_value', $this->helper->getLinksTitle($product));
    }

    public function testGetOptions()
    {
        $item = $this->createMock(ItemInterface::class);
        $product = $this->createPartialMock(
            \Magento\Catalog\Test\Unit\Helper\ProductTestHelper::class,
            ['__wakeup', 'getLinksTitle', 'getTypeInstance']
        );
        $option = $this->createMock(OptionInterface::class);
        $productType = $this->createPartialMock(Type::class, ['getLinks']);
        $productLink = $this->createPartialMock(Link::class, ['getTitle']);

        $this->productConfig->expects($this->once())->method('getOptions')->with($item);
        $item->method('getProduct')->willReturn($product);
        $item->expects($this->once())->method('getOptionByCode')->willReturn($option);
        $product->expects($this->once())->method('getTypeInstance')->willReturn($productType);
        $productType->expects($this->once())->method('getLinks')->with($product)->willReturn([1 => $productLink]);
        $option->method('getValue')->willReturn(1);
        $product->expects($this->once())->method('getLinksTitle')->willReturn('links_title');
        $productLink->expects($this->once())->method('getTitle')->willReturn('title');

        $this->assertEquals([['label' => 'links_title', 'value' => ['title']]], $this->helper->getOptions($item));
    }
}
