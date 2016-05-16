<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
            ->getMock();
        $template->expects($this->once())
            ->method('getProcessedTemplate')
            ->with($this->equalTo([]))
            ->willReturn(self::MALICIOUS_TEXT);
        $designConfigData = [];
        $template->expects($this->atLeastOnce())
            ->method('getDesignConfig')
            ->willReturn(new \Magento\Framework\DataObject(
                $designConfigData
            ));
        $emailFactory = $this->getMock('Magento\Email\Model\TemplateFactory', ['create'], [], '', false);
        $emailFactory->expects($this->any())
            ->method('create')
            ->willReturn($template);

        $request = $this->getMock('Magento\Framework\App\RequestInterface');
        $request->expects($this->any())->method('getParam')->willReturnMap($requestParamMap);
        $eventManage = $this->getMock('Magento\Framework\Event\ManagerInterface');
        $scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $design = $this->getMock('Magento\Framework\View\DesignInterface');
        $store = $this->getMock('Magento\Store\Model\Store', ['getId', '__wakeup'], [], '', false);
        $store->expects($this->any())->method('getId')->willReturn($storeId);
        $storeManager = $this->getMockBuilder('\Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $storeManager->expects($this->atLeastOnce())
            ->method('getDefaultStoreView')
            ->willReturn($store);
        $storeManager->expects($this->any())->method('getDefaultStoreView')->willReturn(null);
        $storeManager->expects($this->any())->method('getStores')->willReturn([$store]);
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
        $context->expects($this->any())->method('getRequest')->willReturn($request);
        $context->expects($this->any())->method('getEventManager')->willReturn($eventManage);
        $context->expects($this->any())->method('getScopeConfig')->willReturn($scopeConfig);
        $context->expects($this->any())->method('getDesignPackage')->willReturn($design);
        $context->expects($this->any())->method('getStoreManager')->willReturn($storeManager);
        $context->expects($this->once())->method('getAppState')->willReturn($appState);

        $maliciousCode = $this->getMock(
            'Magento\Framework\Filter\Input\MaliciousCode',
            ['filter'],
            [],
            '',
            false
        );
        $maliciousCode->expects($this->once())
            ->method('filter')
            ->with($this->equalTo($requestParamMap[1][2]))
            ->willReturn(self::MALICIOUS_TEXT);

        /** @var \Magento\Email\Block\Adminhtml\Template\Preview $preview */
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
