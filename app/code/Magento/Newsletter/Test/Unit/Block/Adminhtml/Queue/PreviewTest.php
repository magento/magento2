<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Test\Unit\Block\Adminhtml\Queue;

class PreviewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Newsletter\Model\Template
     */
    protected $template;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Newsletter\Model\Queue
     */
    protected $queue;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Newsletter\Block\Adminhtml\Queue\Preview
     */
    protected $preview;

    public function setUp()
    {
        $templateFactory = $this->getMock('Magento\Newsletter\Model\TemplateFactory', ['create'], [], '', false);
        $this->template = $this->getMock('Magento\Newsletter\Model\Template', [], [], '', false);
        $templateFactory->expects($this->once())->method('create')->will($this->returnValue($this->template));
        $queueFactory = $this->getMock('Magento\Newsletter\Model\QueueFactory', ['create'], [], '', false);
        $this->queue = $this->getMock('Magento\Newsletter\Model\Queue', ['load'], [], '', false);
        $queueFactory->expects($this->any())->method('create')->will($this->returnValue($this->queue));

        $this->request = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $this->storeManager = $this->getMock(
            'Magento\Store\Model\StoreManager',
            ['getStores', 'getDefaultStoreView'],
            [],
            '',
            false
        );

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->preview = $this->objectManager->getObject(
            'Magento\Newsletter\Block\Adminhtml\Queue\Preview',
            [
                'templateFactory' => $templateFactory,
                'queueFactory' => $queueFactory,
                'request' => $this->request,
                'storeManager' => $this->storeManager
            ]
        );
    }

    public function testToHtmlEmpty()
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->getMock('Magento\Store\Model\Store', ['getId'], [], '', false);
        $this->storeManager->expects($this->once())->method('getDefaultStoreView')->will($this->returnValue($store));
        $result = $this->preview->toHtml();
        $this->assertEquals('', $result);
    }

    public function testToHtmlWithId()
    {
        $this->request->expects($this->any())->method('getParam')->will($this->returnValueMap(
            [
                ['id', null, 1],
                ['store_id', null, 0]
            ]
        ));
        $this->queue->expects($this->once())->method('load')->will($this->returnSelf());
        $this->template->expects($this->any())->method('isPlain')->will($this->returnValue(true));
        /** @var \Magento\Store\Model\Store $store */
        $this->storeManager->expects($this->once())->method('getDefaultStoreView')->will($this->returnValue(null));
        $store = $this->getMock('Magento\Store\Model\Store', ['getId'], [], '', false);
        $this->storeManager->expects($this->once())->method('getStores')->will($this->returnValue([0 => $store]));
        $result = $this->preview->toHtml();
        $this->assertEquals('<pre></pre>', $result);
    }
}
