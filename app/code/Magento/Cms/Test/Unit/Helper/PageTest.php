<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Helper;

/**
 * @covers \Magento\Cms\Helper\Page
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Cms\Helper\Page
     */
    protected $object;

    /**
     * @var \Magento\Framework\App\Action\Action|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $actionMock;

    /**
     * @var \Magento\Cms\Model\PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageFactoryMock;

    /**
     * @var \Magento\Cms\Model\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeDateMock;

    /**
     * @var \Magento\Framework\View\DesignInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $designMock;

    /**
     * @var \Magento\Framework\View\Page\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageConfigMock;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $escaperMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \Magento\Framework\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageMock;

    /**
     * @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * @var \Magento\Framework\View\Layout\ProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutProcessorMock;

    /**
     * @var \Magento\Framework\View\Element\BlockInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $blockMock;

    /**
     * @var \Magento\Framework\View\Element\Messages|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messagesBlockMock;

    /**
     * @var \Magento\Framework\Message\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageCollectionMock;

    /**
     * @var \Magento\Framework\View\Result\PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $httpRequestMock;

    protected function setUp()
    {
        $this->actionMock = $this->getMockBuilder(\Magento\Framework\App\Action\Action::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageFactoryMock = $this->getMockBuilder(\Magento\Cms\Model\PageFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->pageMock = $this->getMockBuilder(\Magento\Cms\Model\Page::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getId',
                    'setStoreId',
                    'load',
                    'getCustomThemeFrom',
                    'getCustomThemeTo',
                    'getCustomTheme',
                    'getPageLayout',
                    'getIdentifier',
                    'getCustomPageLayout',
                    'getCustomLayoutUpdateXml',
                    'getLayoutUpdateXml',
                    'getContentHeading',
                ]
            )
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $this->localeDateMock = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class)
            ->getMockForAbstractClass();
        $this->designMock = $this->getMockBuilder(\Magento\Framework\View\DesignInterface::class)
            ->getMockForAbstractClass();
        $this->pageConfigMock = $this->getMockBuilder(\Magento\Framework\View\Page\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->escaperMock = $this->getMockBuilder(\Magento\Framework\Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventManagerMock = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->getMockForAbstractClass();
        $this->urlBuilderMock = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->getMockForAbstractClass();
        $this->httpRequestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->getMockForAbstractClass();
        $this->storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder(\Magento\Framework\View\Result\Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->layoutMock = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->getMockForAbstractClass();
        $this->layoutProcessorMock = $this->getMockBuilder(\Magento\Framework\View\Layout\ProcessorInterface::class)
            ->getMockForAbstractClass();
        $this->blockMock = $this->getMockBuilder(\Magento\Framework\View\Element\AbstractBlock::class)
            ->setMethods(['setContentHeading'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->messagesBlockMock = $this->getMockBuilder(\Magento\Framework\View\Element\Messages::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->getMockForAbstractClass();
        $this->messageCollectionMock = $this->getMockBuilder(\Magento\Framework\Message\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $context = $objectManager->getObject(
            \Magento\Framework\App\Helper\Context::class,
            [
                'eventManager' => $this->eventManagerMock,
                'urlBuilder' => $this->urlBuilderMock,
                'httpRequest' => $this->httpRequestMock,
            ]
        );

        $this->resultPageFactory = $this->createMock(\Magento\Framework\View\Result\PageFactory::class);

        $this->object = $objectManager->getObject(
            \Magento\Cms\Helper\Page::class,
            [
                'context' => $context,
                'pageFactory' => $this->pageFactoryMock,
                'page' => $this->pageMock,
                'storeManager' => $this->storeManagerMock,
                'localeDate' => $this->localeDateMock,
                'design' => $this->designMock,
                'pageConfig' => $this->pageConfigMock,
                'escaper' => $this->escaperMock,
                'messageManager' => $this->messageManagerMock,
                'resultPageFactory' => $this->resultPageFactory
            ]
        );
    }

    /**
     * @covers \Magento\Cms\Helper\Page::prepareResultPage
     * @param integer|null $pageId
     * @param integer|null $internalPageId
     * @param integer $pageLoadResultIndex
     * @param string $customPageLayout
     * @param string $handle
     * @param string $customLayoutUpdateXml
     * @param string $layoutUpdate
     * @param boolean $expectedResult
     *
     * @dataProvider renderPageExtendedDataProvider
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPrepareResultPage(
        $pageId,
        $internalPageId,
        $pageLoadResultIndex,
        $customPageLayout,
        $handle,
        $customLayoutUpdateXml,
        $layoutUpdate,
        $expectedResult
    ) {
        $storeId = 321;
        $customThemeFrom = 'customThemeFrom';
        $customThemeTo = 'customThemeTo';
        $isScopeDateInInterval = true;
        $customTheme = 'customTheme';
        $pageLayout = 'pageLayout';
        $pageIdentifier = 111;
        $layoutUpdateXml = 'layoutUpdateXml';
        $contentHeading = 'contentHeading';
        $escapedContentHeading = 'escapedContentHeading';
        $pageLoadResultCollection = [
            null,
            $this->pageMock,
        ];

        $this->pageMock->expects($this->any())
            ->method('getId')
            ->willReturn($internalPageId);
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn($storeId);
        $this->pageMock->expects($this->any())
            ->method('setStoreId')
            ->with($storeId)
            ->willReturnSelf();
        $this->pageMock->expects($this->any())
            ->method('load')
            ->with($pageId)
            ->willReturn($pageLoadResultCollection[$pageLoadResultIndex]);
        $this->pageMock->expects($this->any())
            ->method('getCustomThemeFrom')
            ->willReturn($customThemeFrom);
        $this->pageMock->expects($this->any())
            ->method('getCustomThemeTo')
            ->willReturn($customThemeTo);
        $this->localeDateMock->expects($this->any())
            ->method('isScopeDateInInterval')
            ->with(null, $customThemeFrom, $customThemeTo)
            ->willReturn($isScopeDateInInterval);
        $this->pageMock->expects($this->any())
            ->method('getCustomTheme')
            ->willReturn($customTheme);
        $this->designMock->expects($this->any())
            ->method('setDesignTheme')
            ->with($customTheme)
            ->willReturnSelf();
        $this->pageMock->expects($this->any())
            ->method('getPageLayout')
            ->willReturn($pageLayout);
        $this->pageMock->expects($this->any())
            ->method('getCustomPageLayout')
            ->willReturn($customPageLayout);
        $this->resultPageFactory->expects($this->any())->method('create')
            ->will($this->returnValue($this->resultPageMock));
        $this->resultPageMock->expects($this->any())
            ->method('getConfig')
            ->willReturn($this->pageConfigMock);
        $this->pageConfigMock->expects($this->any())
            ->method('setPageLayout')
            ->with($handle)
            ->willReturnSelf();
        $this->resultPageMock->expects($this->any())
            ->method('initLayout')
            ->willReturnSelf();
        $this->resultPageMock->expects($this->any())
            ->method('getLayout')
            ->willReturn($this->layoutMock);
        $this->layoutMock->expects($this->any())
            ->method('getUpdate')
            ->willReturn($this->layoutProcessorMock);
        $this->layoutProcessorMock->expects($this->any())
            ->method('addHandle')
            ->with('cms_page_view')
            ->willReturnSelf();
        $this->pageMock->expects($this->any())
            ->method('getIdentifier')
            ->willReturn($pageIdentifier);
        $this->eventManagerMock->expects($this->any())
            ->method('dispatch')
            ->with(
                'cms_page_render',
                [
                    'page' => $this->pageMock,
                    'controller_action' => $this->actionMock,
                    'request' => $this->httpRequestMock,
                ]
            );
        $this->pageMock->expects($this->any())
            ->method('getCustomLayoutUpdateXml')
            ->willReturn($customLayoutUpdateXml);
        $this->pageMock->expects($this->any())
            ->method('getLayoutUpdateXml')
            ->willReturn($layoutUpdateXml);
        $this->layoutProcessorMock->expects($this->any())
            ->method('addUpdate')
            ->with($layoutUpdate)
            ->willReturnSelf();
        $this->layoutMock->expects($this->any())
            ->method('getBlock')
            ->with('page_content_heading')
            ->willReturn($this->blockMock);
        $this->pageMock->expects($this->any())
            ->method('getContentHeading')
            ->willReturn($contentHeading);
        $this->escaperMock->expects($this->any())
            ->method('escapeHtml')
            ->with($contentHeading)
            ->willReturn($escapedContentHeading);
        $this->blockMock->expects($this->any())
            ->method('setContentHeading')
            ->with($escapedContentHeading)
            ->willReturnSelf();

        if ($expectedResult) {
            $expectedResult = $this->resultPageMock;
        }

        $this->assertSame(
            $expectedResult,
            $this->object->prepareResultPage($this->actionMock, $pageId)
        );
    }

    /**
     * @return array
     */
    public function renderPageExtendedDataProvider()
    {
        return [
            'ids NOT EQUAL BUT page->load() NOT SUCCESSFUL' => [
                'pageId' => 123,
                'internalPageId' => 234,
                'pageLoadResultIndex' => 0,
                'customPageLayout' => 'DOES NOT MATTER',
                'handle' => 'DOES NOT MATTER',
                'customLayoutUpdateXml' => 'DOES NOT MATTER',
                'layoutUpdate' => 'DOES NOT MATTER',
                'expectedResult' => false,
            ],
            'page->load IS SUCCESSFUL BUT internalPageId IS EMPTY' => [
                'pageId' => 123,
                'internalPageId' => null,
                'pageLoadResultIndex' => 1,
                'customPageLayout' => 'DOES NOT MATTER',
                'handle' => 'DOES NOT MATTER',
                'customLayoutUpdateXml' => 'DOES NOT MATTER',
                'layoutUpdate' => 'DOES NOT MATTER',
                'expectedResult' => false,
            ],
            'getPageLayout() AND getLayoutUpdateXml() ARE USED' => [
                'pageId' => 123,
                'internalPageId' => 234,
                'pageLoadResultIndex' => 1,
                'customPageLayout' => 'empty',
                'handle' => 'pageLayout',
                'customLayoutUpdateXml' => '',
                'layoutUpdate' => 'layoutUpdateXml',
                'expectedResult' => true,
            ],
            'getCustomPageLayout() AND getCustomLayoutUpdateXml() ARE USED' => [
                'pageId' => 123,
                'internalPageId' => 234,
                'pageLoadResultIndex' => 1,
                'customPageLayout' => 'customPageLayout',
                'handle' => 'customPageLayout',
                'customLayoutUpdateXml' => 'customLayoutUpdateXml',
                'layoutUpdate' => 'customLayoutUpdateXml',
                'expectedResult' => true,
            ]
        ];
    }

    /**
     * @covers \Magento\Cms\Helper\Page::getPageUrl
     * @param integer|null $pageId
     * @param integer|null $internalPageId
     * @param integer $pageLoadResultIndex
     * @param string|null $expectedResult
     *
     * @dataProvider getPageUrlDataProvider
     */
    public function testGetPageUrl(
        $pageId,
        $internalPageId,
        $pageLoadResultIndex,
        $expectedResult
    ) {
        $storeId = 321;
        $pageIdentifier = 111;
        $url = '/some/url';
        $pageLoadResultCollection = [
            null,
            $this->pageMock,
        ];

        $this->pageFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->pageMock);
        $this->pageMock->expects($this->any())
            ->method('getId')
            ->willReturn($internalPageId);
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn($storeId);
        $this->pageMock->expects($this->any())
            ->method('setStoreId')
            ->with($storeId)
            ->willReturnSelf();
        $this->pageMock->expects($this->any())
            ->method('load')
            ->with($pageId)
            ->willReturn($pageLoadResultCollection[$pageLoadResultIndex]);
        $this->pageMock->expects($this->any())
            ->method('getIdentifier')
            ->willReturn($pageIdentifier);
        $this->urlBuilderMock->expects($this->any())
            ->method('getUrl')
            ->with(null, ['_direct' => $pageIdentifier])
            ->willReturn($url);

        $this->assertEquals($expectedResult, $this->object->getPageUrl($pageId));
    }

    /**
     * @return array
     */
    public function getPageUrlDataProvider()
    {
        return [
            'ids NOT EQUAL BUT page->load() NOT SUCCESSFUL' => [
                'pageId' => 123,
                'internalPageId' => 234,
                'pageLoadResultIndex' => 0,
                'expectedResult' => null,
            ],
            'page->load() IS SUCCESSFUL BUT internalId IS EMPTY' => [
                'pageId' => 123,
                'internalPageId' => null,
                'pageLoadResultIndex' => 1,
                'expectedResult' => null,
            ],
            'SUCCESS' => [
                'pageId' => 123,
                'internalPageId' => 234,
                'pageLoadResultIndex' => 1,
                'expectedResult' => '/some/url',
            ]
        ];
    }
}
