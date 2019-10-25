<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Email\Test\Unit\Block\Adminhtml\Template;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PreviewTest extends \PHPUnit\Framework\TestCase
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
        $template = $this->getMockBuilder(\Magento\Email\Model\Template::class)
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
        $emailFactory = $this->createPartialMock(\Magento\Email\Model\TemplateFactory::class, ['create']);
        $emailFactory->expects($this->any())
            ->method('create')
            ->willReturn($template);

        $request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $request->expects($this->any())->method('getParam')->willReturnMap($requestParamMap);
        $eventManage = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $design = $this->createMock(\Magento\Framework\View\DesignInterface::class);
        $store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getId', '__wakeup']);
        $store->expects($this->any())->method('getId')->willReturn($storeId);
        $storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeManager->expects($this->atLeastOnce())
            ->method('getDefaultStoreView')
            ->willReturn($store);
        $storeManager->expects($this->any())->method('getDefaultStoreView')->willReturn(null);
        $storeManager->expects($this->any())->method('getStores')->willReturn([$store]);
        $appState = $this->getMockBuilder(\Magento\Framework\App\State::class)
            ->setConstructorArgs([
                $scopeConfig
            ])
            ->setMethods(['emulateAreaCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $appState->expects($this->any())
            ->method('emulateAreaCode')
            ->with(\Magento\Email\Model\AbstractTemplate::DEFAULT_DESIGN_AREA, [$template, 'getProcessedTemplate'])
            ->willReturn($template->getProcessedTemplate());

        $context = $this->createPartialMock(
            \Magento\Backend\Block\Template\Context::class,
            ['getRequest', 'getEventManager', 'getScopeConfig', 'getDesignPackage', 'getStoreManager', 'getAppState']
        );
        $context->expects($this->any())->method('getRequest')->willReturn($request);
        $context->expects($this->any())->method('getEventManager')->willReturn($eventManage);
        $context->expects($this->any())->method('getScopeConfig')->willReturn($scopeConfig);
        $context->expects($this->any())->method('getDesignPackage')->willReturn($design);
        $context->expects($this->any())->method('getStoreManager')->willReturn($storeManager);
        $context->expects($this->once())->method('getAppState')->willReturn($appState);

        $maliciousCode = $this->createPartialMock(\Magento\Framework\Filter\Input\MaliciousCode::class, ['filter']);
        $maliciousCode->expects($this->once())
            ->method('filter')
            ->with($this->equalTo($requestParamMap[1][2]))
            ->willReturn(self::MALICIOUS_TEXT);

        /** @var \Magento\Email\Block\Adminhtml\Template\Preview $preview */
        $preview = $this->objectManagerHelper->getObject(
            \Magento\Email\Block\Adminhtml\Template\Preview::class,
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
                ['text', null, self::MALICIOUS_TEXT],
                ['styles', null, ''],
            ]],
        ];
    }
}
