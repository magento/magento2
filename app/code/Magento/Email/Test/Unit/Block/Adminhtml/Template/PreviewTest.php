<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Email\Test\Unit\Block\Adminhtml\Template;

class PreviewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    const MALICIOUS_TEXT = 'test malicious';

    /**
     * Init data
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
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
        $storeId = 1;
        $template = $this->getMockBuilder('Magento\Email\Model\Template')
            ->setMethods([
                'setDesignConfig',
                'getDesignConfig',
                '__wakeup',
                'getProcessedTemplate',
                'getAppState',
                'revertDesign'
            ])
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $template->expects($this->once())
            ->method('getProcessedTemplate')
            ->with($this->equalTo([]))
            ->will($this->returnValue(self::MALICIOUS_TEXT));
        $designConfigData = [
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'store' => $storeId
        ];
        $template->expects($this->atLeastOnce())
            ->method('getDesignConfig')
            ->will($this->returnValue(new \Magento\Framework\Object(
                $designConfigData
            )));
        $emailFactory = $this->getMock('Magento\Email\Model\TemplateFactory', ['create'], [], '', false);
        $emailFactory->expects($this->once())
            ->method('create')
            ->with($this->equalTo(['data' => $designConfigData]))
            ->will($this->returnValue($template));

        $request = $this->getMock('Magento\Framework\App\RequestInterface');
        $request->expects($this->any())->method('getParam')->will($this->returnValueMap($requestParamMap));
        $eventManage = $this->getMock('Magento\Framework\Event\ManagerInterface');
        $scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $design = $this->getMock('Magento\Framework\View\DesignInterface');
        $store = $this->getMock('Magento\Store\Model\Store', ['getId', '__wakeup'], [], '', false);
        $store->expects($this->any())->method('getId')->will($this->returnValue($storeId));
        $storeManager = $this->getMockBuilder('\Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $storeManager->expects($this->atLeastOnce())
            ->method('getDefaultStoreView')
            ->will($this->returnValue($store));
        $storeManager->expects($this->any())->method('getDefaultStoreView')->will($this->returnValue(null));
        $storeManager->expects($this->any())->method('getStores')->will($this->returnValue([$store]));
        $appState = $this->getMockBuilder('Magento\Framework\App\State')
            ->setConstructorArgs([
                $scopeConfig
            ])
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $context = $this->getMock('Magento\Backend\Block\Template\Context',
            ['getRequest', 'getEventManager', 'getScopeConfig', 'getDesignPackage', 'getStoreManager', 'getAppState'],
            [], '', false
        );
        $context->expects($this->any())->method('getRequest')->will($this->returnValue($request));
        $context->expects($this->any())->method('getEventManager')->will($this->returnValue($eventManage));
        $context->expects($this->any())->method('getScopeConfig')->will($this->returnValue($scopeConfig));
        $context->expects($this->any())->method('getDesignPackage')->will($this->returnValue($design));
        $context->expects($this->any())->method('getStoreManager')->will($this->returnValue($storeManager));
        $context->expects($this->once())->method('getAppState')->will($this->returnValue($appState));

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
                'maliciousCode' => $maliciousCode,
                'emailFactory' => $emailFactory
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
}
