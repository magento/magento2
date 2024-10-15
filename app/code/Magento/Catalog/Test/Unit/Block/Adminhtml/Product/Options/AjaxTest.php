<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AjaxTest extends TestCase
{
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
        $this->context = $this->getMockBuilder(Context::class)
            ->onlyMethods(['getEventManager', 'getScopeConfig', 'getLayout', 'getRequest'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->encoderInterface = $this->getMockForAbstractClass(EncoderInterface::class);
        $this->productFactory = $this->createPartialMock(ProductFactory::class, ['create']);
        $this->registry = $this->createMock(Registry::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    /**
     *  Test protected `_toHtml` method via public `toHtml` method.
     */
    public function testToHtml()
    {
        $eventManager = $this->getMockBuilder(Manager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['dispatch'])
            ->getMock();
        $eventManager->expects($this->exactly(2))->method('dispatch')->willReturn(true);

        $scopeConfig = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();
        $scopeConfig->expects($this->once())->method('getValue')->withAnyParameters()
            ->willReturn(false);

        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setStoreId', 'load', 'getId', '__sleep'])
            ->getMock();
        $product->expects($this->once())->method('setStoreId')->willReturnSelf();
        $product->expects($this->once())->method('load')->willReturnSelf();
        $product->expects($this->once())->method('getId')->willReturn(1);

        $optionsBlock = $this->getMockBuilder(Option::class)
            ->addMethods(['setIgnoreCaching'])
            ->onlyMethods(['setProduct', 'getOptionValues'])
            ->disableOriginalConstructor()
            ->getMock();
        $optionsBlock->expects($this->once())->method('setIgnoreCaching')->with(true)->willReturnSelf();
        $optionsBlock->expects($this->once())->method('setProduct')->with($product)->willReturnSelf();
        $optionsBlock->expects($this->once())->method('getOptionValues')->willReturn([]);

        $layout = $this->getMockBuilder(LayoutInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createBlock'])
            ->getMockForAbstractClass();
        $layout->expects($this->once())->method('createBlock')
            ->with(Option::class)
            ->willReturn($optionsBlock);

        $request = $this->getMockBuilder(Http::class)
            ->onlyMethods(['getParam'])
            ->disableOriginalConstructor()
            ->getMock();
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
