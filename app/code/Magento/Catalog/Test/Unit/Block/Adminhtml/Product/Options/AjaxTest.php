<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Options;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AjaxTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Catalog\Block\Adminhtml\Product\Options\Ajax */
    protected $block;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Backend\Block\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var \Magento\Framework\Json\EncoderInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $encoderInterface;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $productFactory;

    /** @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject */
    protected $registry;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->context = $this->getMockBuilder(\Magento\Backend\Block\Context::class)
            ->setMethods(['getEventManager', 'getScopeConfig', 'getLayout', 'getRequest'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->encoderInterface = $this->createMock(\Magento\Framework\Json\EncoderInterface::class);
        $this->productFactory = $this->createPartialMock(\Magento\Catalog\Model\ProductFactory::class, ['create']);
        $this->registry = $this->createMock(\Magento\Framework\Registry::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    /**
     *  Test protected `_toHtml` method via public `toHtml` method.
     */
    public function testToHtml()
    {
        $eventManager = $this->getMockBuilder(\Magento\Framework\Event\Manager::class)
            ->disableOriginalConstructor()
            ->setMethods(['dispatch'])
            ->getMock();
        $eventManager->expects($this->exactly(2))->method('dispatch')->will($this->returnValue(true));

        $scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config::class)
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()->getMock();
        $scopeConfig->expects($this->once())->method('getValue')->withAnyParameters()
            ->will($this->returnValue(false));

        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)->disableOriginalConstructor()
            ->setMethods(['setStoreId', 'load', 'getId', '__wakeup', '__sleep'])
            ->getMock();
        $product->expects($this->once())->method('setStoreId')->will($this->returnSelf());
        $product->expects($this->once())->method('load')->will($this->returnSelf());
        $product->expects($this->once())->method('getId')->will($this->returnValue(1));

        $optionsBlock = $this->getMockBuilder(\Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Option::class)
            ->setMethods(['setIgnoreCaching', 'setProduct', 'getOptionValues'])
            ->disableOriginalConstructor()
            ->getMock();
        $optionsBlock->expects($this->once())->method('setIgnoreCaching')->with(true)->will($this->returnSelf());
        $optionsBlock->expects($this->once())->method('setProduct')->with($product)->will($this->returnSelf());
        $optionsBlock->expects($this->once())->method('getOptionValues')->will($this->returnValue([]));

        $layout = $this->getMockBuilder(\Magento\Framework\View\Layout\Element\Layout::class)
            ->disableOriginalConstructor()
            ->setMethods(['createBlock'])
            ->getMock();
        $layout->expects($this->once())->method('createBlock')
            ->with(\Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Option::class)
            ->will($this->returnValue($optionsBlock));

        $request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
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
            \Magento\Catalog\Block\Adminhtml\Product\Options\Ajax::class,
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
