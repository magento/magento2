<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Options;

use Magento\Backend\Block\Context;
use Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Option;
use Magento\Catalog\Block\Adminhtml\Product\Options\Ajax;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Config;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Manager;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AjaxTest extends TestCase
{
    use MockCreationTrait;
    /** @var Ajax */
    protected $block;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var Context|MockObject */
    protected $context;

    /** @var EncoderInterface|MockObject */
    protected $encoderInterface;

    /** @var MockObject */
    protected $productFactory;

    /** @var Registry|MockObject */
    protected $registry;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->context = $this->createPartialMock(
            Context::class,
            ['getEventManager', 'getScopeConfig', 'getLayout', 'getRequest']
        );
        $this->encoderInterface = $this->createMock(EncoderInterface::class);
        $this->productFactory = $this->createPartialMock(ProductFactory::class, ['create']);
        $this->registry = $this->createMock(Registry::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    /**
     *  Test protected `_toHtml` method via public `toHtml` method.
     */
    public function testToHtml()
    {
        $eventManager = $this->createPartialMock(Manager::class, ['dispatch']);
        $eventManager->expects($this->exactly(2))->method('dispatch')->willReturn(true);

        $scopeConfig = $this->createPartialMock(Config::class, ['getValue']);
        $scopeConfig->expects($this->once())->method('getValue')->withAnyParameters()
            ->willReturn(false);

        $product = $this->createPartialMock(Product::class, ['setStoreId', 'load', 'getId', '__sleep']);
        $product->expects($this->once())->method('setStoreId')->willReturnSelf();
        $product->expects($this->once())->method('load')->willReturnSelf();
        $product->expects($this->once())->method('getId')->willReturn(1);

        $mockProduct = $this->createPartialMock(Product::class, ['getOptions']);
        $mockProduct->method('getOptions')->willReturn([]);
        
        $optionsBlock = $this->createPartialMockWithReflection(
            Option::class,
            ['setIgnoreCaching', 'setProduct', 'getChildHtml', 'getProduct', 'toHtml', 'getOptionValues']
        );
        $optionsBlock->expects($this->once())->method('setIgnoreCaching')->with(true)->willReturnSelf();
        $optionsBlock->expects($this->once())->method('setProduct')->with($product)->willReturnSelf();
        $optionsBlock->method('getChildHtml')->willReturn('');
        $optionsBlock->method('getProduct')->willReturn($mockProduct);
        $optionsBlock->method('toHtml')->willReturn('');
        $optionsBlock->expects($this->once())->method('getOptionValues')->willReturn([]);

        $layout = $this->createMock(LayoutInterface::class);
        $layout->expects($this->once())->method('createBlock')
            ->with(Option::class)
            ->willReturn($optionsBlock);

        $request = $this->createPartialMock(Http::class, ['getParam']);
        $request->expects($this->once())->method('getParam')->with('store')
            ->willReturn(0);

        $this->context->expects($this->once())->method('getEventManager')
            ->willReturn($eventManager);
        $this->context->expects($this->once())->method('getScopeConfig')
            ->willReturn($scopeConfig);
        $this->context->expects($this->once())->method('getLayout')
            ->willReturn($layout);
        $this->context->expects($this->once())->method('getRequest')
            ->willReturn($request);
        $this->registry->expects($this->once())->method('registry')
            ->with('import_option_products')
            ->willReturn([1]);
        $this->productFactory->expects($this->once())->method('create')->willReturn($product);

        $this->block = $this->objectManagerHelper->getObject(
            Ajax::class,
            [
                'context' => $this->context,
                'jsonEncoder' => $this->encoderInterface,
                'productFactory' => $this->productFactory,
                'registry' => $this->registry
            ]
        );
        $this->block->toHtml();
    }
}
