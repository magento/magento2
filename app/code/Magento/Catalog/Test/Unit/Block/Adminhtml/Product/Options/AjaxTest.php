<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

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
            ->setMethods(['getEventManager', 'getScopeConfig', 'getLayout', 'getRequest'])
            ->disableOriginalConstructor()
            ->getMock();
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
        $eventManager = $this->getMockBuilder(Manager::class)
            ->disableOriginalConstructor()
            ->setMethods(['dispatch'])
            ->getMock();
        $eventManager->expects($this->exactly(2))->method('dispatch')->will($this->returnValue(true));

        $scopeConfig = $this->getMockBuilder(Config::class)
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()->getMock();
        $scopeConfig->expects($this->once())->method('getValue')->withAnyParameters()
            ->will($this->returnValue(false));

        $product = $this->getMockBuilder(Product::class)->disableOriginalConstructor()
            ->setMethods(['setStoreId', 'load', 'getId', '__wakeup', '__sleep'])
            ->getMock();
        $product->expects($this->once())->method('setStoreId')->will($this->returnSelf());
        $product->expects($this->once())->method('load')->will($this->returnSelf());
        $product->expects($this->once())->method('getId')->will($this->returnValue(1));

        $optionsBlock = $this->getMockBuilder(Option::class)
            ->setMethods(['setIgnoreCaching', 'setProduct', 'getOptionValues'])
            ->disableOriginalConstructor()
            ->getMock();
        $optionsBlock->expects($this->once())->method('setIgnoreCaching')->with(true)->will($this->returnSelf());
        $optionsBlock->expects($this->once())->method('setProduct')->with($product)->will($this->returnSelf());
        $optionsBlock->expects($this->once())->method('getOptionValues')->will($this->returnValue([]));

        $layout = $this->getMockBuilder(LayoutInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['createBlock'])
            ->getMockForAbstractClass();
        $layout->expects($this->once())->method('createBlock')
            ->with(Option::class)
            ->will($this->returnValue($optionsBlock));

        $request = $this->getMockBuilder(Http::class)
            ->setMethods(['getParam'])
            ->disableOriginalConstructor()
            ->getMock();
        $request->expects($this->once())->method('getParam')->with('store')
            ->will($this->returnValue(0));

        $this->context->expects($this->once())->method('getEventManager')
            ->will($this->returnValue($eventManager));
        $this->context->expects($this->once())->method('getScopeConfig')
            ->will($this->returnValue($scopeConfig));
        $this->context->expects($this->once())->method('getLayout')
            ->will($this->returnValue($layout));
        $this->context->expects($this->once())->method('getRequest')
            ->will($this->returnValue($request));
        $this->registry->expects($this->once())->method('registry')
            ->with('import_option_products')
            ->will($this->returnValue([1]));
        $this->productFactory->expects($this->once())->method('create')->will($this->returnValue($product));

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
