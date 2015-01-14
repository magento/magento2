<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Block\Adminhtml\Template;

class PreviewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    const MALICIOUS_TEXT = 'test malicious';

    /**
     * Init data
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    /**
     * Check of processing email templates
     *
     * @param array $requestParamMap
     *
     * @dataProvider toHtmlDataProvider
     * @param $requestParamMap
     */
    public function testToHtml($requestParamMap)
    {
        $template = $this->getMock('Magento\Email\Model\Template',
            ['setDesignConfig', 'getDesignConfig', '__wakeup', 'getProcessedTemplate'], [], '', false);
        $template->expects($this->once())
            ->method('getProcessedTemplate')
            ->with($this->equalTo([]), $this->equalTo(true))
            ->will($this->returnValue(self::MALICIOUS_TEXT));
        $emailFactory = $this->getMock('Magento\Email\Model\TemplateFactory', ['create'], [], '', false);
        $emailFactory->expects($this->once())
            ->method('create')
            ->with($this->equalTo(['data' => ['area' => \Magento\Framework\App\Area::AREA_FRONTEND]]))
            ->will($this->returnValue($template));

        $request = $this->getMock('Magento\Framework\App\RequestInterface');
        $request->expects($this->any())->method('getParam')->will($this->returnValueMap($requestParamMap));
        $eventManage = $this->getMock('Magento\Framework\Event\ManagerInterface');
        $scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $design = $this->getMock('Magento\Framework\View\DesignInterface');
        $store = $this->getMock('Magento\Store\Model\Store', ['getId', '__wakeup'], [], '', false);
        $store->expects($this->any())->method('getId')->will($this->returnValue(1));
        $storeManager = $this->getMock('\Magento\Store\Model\StoreManagerInterface');
        $storeManager->expects($this->any())->method('getDefaultStoreView')->will($this->returnValue(null));
        $storeManager->expects($this->any())->method('getStores')->will($this->returnValue([$store]));

        $context = $this->getMock('Magento\Backend\Block\Template\Context',
            ['getRequest', 'getEventManager', 'getScopeConfig', 'getDesignPackage', 'getStoreManager'],
            [], '', false
        );
        $context->expects($this->any())->method('getRequest')->will($this->returnValue($request));
        $context->expects($this->any())->method('getEventManager')->will($this->returnValue($eventManage));
        $context->expects($this->any())->method('getScopeConfig')->will($this->returnValue($scopeConfig));
        $context->expects($this->any())->method('getDesignPackage')->will($this->returnValue($design));
        $context->expects($this->any())->method('getStoreManager')->will($this->returnValue($storeManager));

        $maliciousCode = $this->getMock(
            'Magento\Framework\Filter\Input\MaliciousCode',
            ['filter'],
            [],
            '',
            false
        );
        $maliciousCode->expects($this->once())->method('filter')->with($this->equalTo($requestParamMap[1][2]))
            ->will($this->returnValue(self::MALICIOUS_TEXT));

        $preview = $this->objectManagerHelper->getObject(
            'Magento\Email\Block\Adminhtml\Template\Preview',
            [
                'context' => $context,
                'emailFactory' => $emailFactory,
                'maliciousCode' => $maliciousCode
            ]
        );
        $this->assertEquals(self::MALICIOUS_TEXT, $preview->toHtml());
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function toHtmlDataProvider()
    {
        return [
            ['data 1' => [
                ['type', null, ''],
                ['text', null, sprintf('<javascript>%s</javascript>', self::MALICIOUS_TEXT)],
                ['styles', null, ''],
            ]],
            ['data 2' => [
                ['type', null, ''],
                ['text', null, sprintf('<iframe>%s</iframe>', self::MALICIOUS_TEXT)],
                ['styles', null, ''],
            ]],
            ['data 3' => [
                ['type', null, ''],
                ['text', null, self::MALICIOUS_TEXT],
                ['styles', null, ''],
            ]],
        ];
    }

    /**
     * Test exception with no store found
     *
     * @expectedException \Magento\Framework\Exception
     * @expectedExceptionMessage Design config must have area and store.
     */
    public function testToHtmlWithException()
    {
        $template = $this->getMock('Magento\Email\Model\Template',
            ['__wakeup', 'load'], [], '', false);
        $template->expects($this->once())
            ->method('load')
            ->with($this->equalTo(1))
            ->will($this->returnSelf());
        $emailFactory = $this->getMock('Magento\Email\Model\TemplateFactory', ['create'], [], '', false);
        $emailFactory->expects($this->once())
            ->method('create')
            ->with($this->equalTo(['data' => ['area' => \Magento\Framework\App\Area::AREA_FRONTEND]]))
            ->will($this->returnValue($template));

        $request = $this->getMock('Magento\Framework\App\RequestInterface');
        $request->expects($this->any())->method('getParam')->with($this->equalTo('id'))->will($this->returnValue(1));
        $eventManage = $this->getMock('Magento\Framework\Event\ManagerInterface');
        $scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $design = $this->getMock('Magento\Framework\View\DesignInterface');
        $storeManager = $this->getMock('\Magento\Store\Model\StoreManagerInterface');
        $storeManager->expects($this->any())->method('getDefaultStoreView')->will($this->returnValue(null));
        $storeManager->expects($this->any())->method('getStores')->will($this->returnValue([]));

        $context = $this->getMock('Magento\Backend\Block\Template\Context',
            ['getRequest', 'getEventManager', 'getScopeConfig', 'getDesignPackage', 'getStoreManager'],
            [], '', false
        );
        $context->expects($this->any())->method('getRequest')->will($this->returnValue($request));
        $context->expects($this->any())->method('getEventManager')->will($this->returnValue($eventManage));
        $context->expects($this->any())->method('getScopeConfig')->will($this->returnValue($scopeConfig));
        $context->expects($this->any())->method('getDesignPackage')->will($this->returnValue($design));
        $context->expects($this->any())->method('getStoreManager')->will($this->returnValue($storeManager));

        $maliciousCode = $this->getMock(
            'Magento\Framework\Filter\Input\MaliciousCode',
            ['filter'],
            [],
            '',
            false
        );
        $maliciousCode->expects($this->once())->method('filter')
            ->will($this->returnValue(''));

        $preview = $this->objectManagerHelper->getObject(
            'Magento\Email\Block\Adminhtml\Template\Preview',
            [
                'context' => $context,
                'emailFactory' => $emailFactory,
                'maliciousCode' => $maliciousCode
            ]
        );
        $preview->toHtml();
    }
}
