<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Email\Test\Unit\Block\Adminhtml\Template;

use Magento\Backend\Block\Template\Context;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Email\Block\Adminhtml\Template\Preview;
use Magento\Email\Model\AbstractTemplate;
use Magento\Email\Model\Template;
use Magento\Email\Model\TemplateFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\State;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Filter\Input\MaliciousCode;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PreviewTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManagerHelper;

    const MALICIOUS_TEXT = 'test malicious';

    /**
     * @var Http|MockObject
     */
    protected $request;

    /**
     * @var Preview
     */
    protected $preview;

    /**
     * @var MaliciousCode|MockObject
     */
    protected $maliciousCode;

    /**
     * @var Template|MockObject
     */
    protected $template;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManager;

    /**
     * Init data
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManager($this);

        $storeId = 1;
        $designConfigData = [];

        $this->template = $this->getMockBuilder(Template::class)
            ->addMethods(['getAppState'])
            ->onlyMethods(
                [
                    'setDesignConfig',
                    'getDesignConfig',
                    'getProcessedTemplate',
                    'revertDesign'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->request = $this->createMock(Http::class);

        $this->maliciousCode = $this->createPartialMock(
            MaliciousCode::class,
            ['filter']
        );

        $this->template->expects($this->once())
            ->method('getProcessedTemplate')
            ->with([])
            ->willReturn(self::MALICIOUS_TEXT);

        $this->template->method('getDesignConfig')
            ->willReturn(new DataObject($designConfigData));

        $emailFactory = $this->createPartialMock(TemplateFactory::class, ['create']);
        $emailFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->template);

        $eventManage = $this->getMockForAbstractClass(ManagerInterface::class);
        $scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $design = $this->getMockForAbstractClass(DesignInterface::class);
        $store = $this->createPartialMock(Store::class, ['getId']);

        $store->expects($this->any())
            ->method('getId')
            ->willReturn($storeId);

        $this->storeManager->method('getDefaultStoreView')
            ->willReturn($store);

        $this->storeManager->expects($this->any())->method('getDefaultStoreView')->willReturn(null);
        $this->storeManager->expects($this->any())->method('getStores')->willReturn([$store]);
        $appState = $this->getMockBuilder(State::class)
            ->setConstructorArgs(
                [
                    $scopeConfig
                ]
            )
            ->onlyMethods(['emulateAreaCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $appState->expects($this->any())
            ->method('emulateAreaCode')
            ->with(
                AbstractTemplate::DEFAULT_DESIGN_AREA,
                [$this->template, 'getProcessedTemplate']
            )
            ->willReturn($this->template->getProcessedTemplate());

        $context = $this->createPartialMock(
            Context::class,
            ['getRequest', 'getEventManager', 'getScopeConfig', 'getDesignPackage', 'getStoreManager', 'getAppState']
        );
        $context->expects($this->any())->method('getRequest')->willReturn($this->request);
        $context->expects($this->any())->method('getEventManager')->willReturn($eventManage);
        $context->expects($this->any())->method('getScopeConfig')->willReturn($scopeConfig);
        $context->expects($this->any())->method('getDesignPackage')->willReturn($design);
        $context->expects($this->any())->method('getStoreManager')->willReturn($this->storeManager);
        $context->expects($this->once())->method('getAppState')->willReturn($appState);
        $objects = [
            [
                JsonHelper::class,
                $this->createMock(JsonHelper::class)
            ],
            [
                DirectoryHelper::class,
                $this->createMock(DirectoryHelper::class)
            ]
        ];
        $this->objectManagerHelper->prepareObjectManager($objects);

        /** @var Preview $preview */
        $this->preview = $this->objectManagerHelper->getObject(
            Preview::class,
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
            ->with($requestParamMap[1][2])
            ->willReturn(self::MALICIOUS_TEXT);

        $this->assertEquals(self::MALICIOUS_TEXT, $this->preview->toHtml());
    }

    /**
     * Data provider
     *
     * @return array
     */
    public static function toHtmlDataProvider()
    {
        return [
            ['requestParamMap' => [
                ['type', null, ''],
                ['text', null, self::MALICIOUS_TEXT],
                ['styles', null, ''],
            ]],
        ];
    }
}
