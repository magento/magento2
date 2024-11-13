<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Helper;

use Magento\Cms\Helper\Page;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Message\Collection;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Element\Messages;
use Magento\Framework\View\Layout\ProcessorInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Page\Config;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Cms\Helper\Page
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PageTest extends TestCase
{
    /**
     * @var Page
     */
    protected $object;

    /**
     * @var Action|MockObject
     */
    protected $actionMock;

    /**
     * @var PageFactory|MockObject
     */
    protected $pageFactoryMock;

    /**
     * @var \Magento\Cms\Model\Page|MockObject
     */
    protected $pageMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManagerMock;

    /**
     * @var TimezoneInterface|MockObject
     */
    protected $localeDateMock;

    /**
     * @var DesignInterface|MockObject
     */
    protected $designMock;

    /**
     * @var Config|MockObject
     */
    protected $pageConfigMock;

    /**
     * @var Escaper|MockObject
     */
    protected $escaperMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|MockObject
     */
    protected $eventManagerMock;

    /**
     * @var UrlInterface|MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var Store|MockObject
     */
    protected $storeMock;

    /**
     * @var \Magento\Framework\View\Result\Page|MockObject
     */
    protected $resultPageMock;

    /**
     * @var LayoutInterface|MockObject
     */
    protected $layoutMock;

    /**
     * @var ProcessorInterface|MockObject
     */
    protected $layoutProcessorMock;

    /**
     * @var BlockInterface|MockObject
     */
    protected $blockMock;

    /**
     * @var Messages|MockObject
     */
    protected $messagesBlockMock;

    /**
     * @var Collection|MockObject
     */
    protected $messageCollectionMock;

    /**
     * @var \Magento\Framework\View\Result\PageFactory|MockObject
     */
    protected $resultPageFactory;

    /**
     * @var RequestInterface|MockObject
     */
    private $httpRequestMock;

    /**
     * Test Setup
     */
    protected function setUp(): void
    {
        $this->actionMock = $this->getMockBuilder(Action::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->pageMock = $this->getMockBuilder(\Magento\Cms\Model\Page::class)
            ->disableOriginalConstructor()
            ->addMethods(['setStoreId', 'getCustomPageLayout'])
            ->onlyMethods(
                [
                    'getId',
                    'load',
                    'getCustomThemeFrom',
                    'getCustomThemeTo',
                    'getCustomTheme',
                    'getPageLayout',
                    'getIdentifier',
                    'getCustomLayoutUpdateXml',
                    'getLayoutUpdateXml',
                    'getContentHeading',
                ]
            )
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $this->localeDateMock = $this->getMockBuilder(TimezoneInterface::class)
            ->getMockForAbstractClass();
        $this->designMock = $this->getMockBuilder(DesignInterface::class)
            ->getMockForAbstractClass();
        $this->pageConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->escaperMock = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventManagerMock = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->getMockForAbstractClass();
        $this->urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->getMockForAbstractClass();
        $this->httpRequestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();
        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder(\Magento\Framework\View\Result\Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->layoutMock = $this->getMockBuilder(LayoutInterface::class)
            ->getMockForAbstractClass();
        $this->layoutProcessorMock = $this->getMockBuilder(ProcessorInterface::class)
            ->getMockForAbstractClass();
        $this->blockMock = $this->getMockBuilder(AbstractBlock::class)
            ->addMethods(['setContentHeading'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->messagesBlockMock = $this->getMockBuilder(Messages::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMockForAbstractClass();
        $this->messageCollectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $context = $objectManager->getObject(
            Context::class,
            [
                'eventManager' => $this->eventManagerMock,
                'urlBuilder' => $this->urlBuilderMock,
                'httpRequest' => $this->httpRequestMock,
            ]
        );

        $this->resultPageFactory = $this->createMock(\Magento\Framework\View\Result\PageFactory::class);

        $this->object = $objectManager->getObject(
            Page::class,
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
            ->willReturn($this->resultPageMock);
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
    public static function renderPageExtendedDataProvider()
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
    public static function getPageUrlDataProvider()
    {
        return [
            'ids NOT EQUAL BUT page->load() NOT SUCCESSFUL' => [
                'pageId' => 123,
                'internalPageId' => null,
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
