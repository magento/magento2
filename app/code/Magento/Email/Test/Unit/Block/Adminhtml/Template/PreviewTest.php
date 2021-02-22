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
     * @var \Magento\Framework\App\Request\Http|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $request;

    /**
     * @var \Magento\Email\Block\Adminhtml\Template\Preview
     */
    protected $preview;

    /**
     * @var \Magento\Framework\Filter\Input\MaliciousCode|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $maliciousCode;

    /**
     * @var \Magento\Email\Model\Template|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $template;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManager;

    /**
     * Init data
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $storeId = 1;
        $designConfigData = [];

        $this->template = $this->getMockBuilder(\Magento\Email\Model\Template::class)
            ->setMethods(
                [
                    'setDesignConfig',
                    'getDesignConfig',
                    '__wakeup',
                    'getProcessedTemplate',
                    'getAppState',
                    'revertDesign'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->createMock(\Magento\Framework\App\Request\Http::class);

        $this->maliciousCode = $this->createPartialMock(
            \Magento\Framework\Filter\Input\MaliciousCode::class,
            ['filter']
        );

        $this->template->expects($this->once())
            ->method('getProcessedTemplate')
            ->with($this->equalTo([]))
            ->willReturn(self::MALICIOUS_TEXT);

        $this->template->method('getDesignConfig')
            ->willReturn(new \Magento\Framework\DataObject($designConfigData));

        $emailFactory = $this->createPartialMock(\Magento\Email\Model\TemplateFactory::class, ['create']);
        $emailFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->template);

        $eventManage = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $design = $this->createMock(\Magento\Framework\View\DesignInterface::class);
        $store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getId', '__wakeup']);

        $store->expects($this->any())
            ->method('getId')
            ->willReturn($storeId);

        $this->storeManager->method('getDefaultStoreView')
            ->willReturn($store);

        $this->storeManager->expects($this->any())->method('getDefaultStoreView')->willReturn(null);
        $this->storeManager->expects($this->any())->method('getStores')->willReturn([$store]);
        $appState = $this->getMockBuilder(\Magento\Framework\App\State::class)
            ->setConstructorArgs(
                [
                    $scopeConfig
                ]
            )
            ->setMethods(['emulateAreaCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $appState->expects($this->any())
            ->method('emulateAreaCode')
            ->with(
                \Magento\Email\Model\AbstractTemplate::DEFAULT_DESIGN_AREA,
                [$this->template, 'getProcessedTemplate']
            )
            ->willReturn($this->template->getProcessedTemplate());

        $context = $this->createPartialMock(
            \Magento\Backend\Block\Template\Context::class,
            ['getRequest', 'getEventManager', 'getScopeConfig', 'getDesignPackage', 'getStoreManager', 'getAppState']
        );
        $context->expects($this->any())->method('getRequest')->willReturn($this->request);
        $context->expects($this->any())->method('getEventManager')->willReturn($eventManage);
        $context->expects($this->any())->method('getScopeConfig')->willReturn($scopeConfig);
        $context->expects($this->any())->method('getDesignPackage')->willReturn($design);
        $context->expects($this->any())->method('getStoreManager')->willReturn($this->storeManager);
        $context->expects($this->once())->method('getAppState')->willReturn($appState);

        /** @var \Magento\Email\Block\Adminhtml\Template\Preview $preview */
        $this->preview = $this->objectManagerHelper->getObject(
            \Magento\Email\Block\Adminhtml\Template\Preview::class,
            [
                'context' => $context,
                'maliciousCode' => $this->maliciousCode,
                'emailFactory' => $emailFactory
            ]
        );
    }

    /**
     * Check of processing email templates
     *
     * @param array $requestParamMap
     * @dataProvider toHtmlDataProvider
     */
    public function testToHtml($requestParamMap)
    {
        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturnMap($requestParamMap);
        $this->template
            ->expects($this->atLeastOnce())
            ->method('getDesignConfig');
        $this->storeManager->expects($this->atLeastOnce())
            ->method('getDefaultStoreView');
        $this->maliciousCode->expects($this->once())
            ->method('filter')
            ->with($this->equalTo($requestParamMap[1][2]))
            ->willReturn(self::MALICIOUS_TEXT);

        $this->assertEquals(self::MALICIOUS_TEXT, $this->preview->toHtml());
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
