<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Category;

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Controller\Category\View;
use Magento\Catalog\Helper\Category;
use Magento\Catalog\Model\Category\Attribute\LayoutUpdateManager;
use Magento\Catalog\Model\Design;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Product\ProductList\ToolbarMemorizer;
use Magento\Catalog\Model\Session;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Layout\ProcessorInterface;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ViewTest extends TestCase
{
    /**
     * @var View
     */
    private $action;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var ResponseInterface|MockObject
     */
    private $responseMock;

    /**
     * @var Category|MockObject
     */
    private $categoryHelperMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $eventManagerMock;

    /**
     * @var Layout|MockObject
     */
    private $layoutMock;

    /**
     * @var ProcessorInterface|MockObject
     */
    private $updateMock;

    /**
     * @var ViewInterface|MockObject
     */
    private $viewMock;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var \Magento\Catalog\Model\Category|MockObject
     */
    private $categoryMock;

    /**
     * @var CategoryRepositoryInterface|MockObject
     */
    private $categoryRepositoryMock;

    /**
     * @var Store|MockObject
     */
    private $storeMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var Design|MockObject
     */
    private $catalogDesignMock;

    /**
     * @var ResultFactory|MockObject
     */
    private $resultFactoryMock;

    /**
     * @var Page|MockObject
     */
    private $pageMock;

    /**
     * @var Config|MockObject
     */
    private $pageConfigMock;

    /**
     * Set up instances and mock objects
     */
    protected function setUp(): void
    {
        $this->requestMock = $this->getMockForAbstractClass(
            RequestInterface::class,
            [],
            '',
            true,
            true,
            true,
            ['getPathInfo']
        );
        $this->requestMock->method('getPathInfo')->willReturn('/category.html');
        $this->responseMock = $this->getMockForAbstractClass(ResponseInterface::class);

        $this->categoryHelperMock = $this->createMock(Category::class);
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);

        $this->updateMock = $this->getMockForAbstractClass(ProcessorInterface::class);
        $this->layoutMock = $this->createMock(Layout::class);
        $this->layoutMock->method('getUpdate')->willReturn($this->updateMock);

        $this->pageConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfigMock->method('addBodyClass')->willReturnSelf();

        $this->pageMock = $this->getMockBuilder(Page::class)
            ->setMethods(['getConfig', 'initLayout', 'addPageLayoutHandles', 'getLayout', 'addUpdate'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageMock->method('getConfig')->willReturn($this->pageConfigMock);
        $this->pageMock->method('addPageLayoutHandles')->willReturnSelf();
        $this->pageMock->method('getLayout')->willReturn($this->layoutMock);
        $this->pageMock->method('addUpdate')->willReturnSelf();

        $this->viewMock = $this->getMockForAbstractClass(ViewInterface::class);
        $this->viewMock->method('getLayout')->willReturn($this->layoutMock);

        $this->resultFactoryMock = $this->createMock(ResultFactory::class);
        $this->resultFactoryMock->method('create')->willReturn($this->pageMock);

        $this->contextMock = $this->createMock(Context::class);
        $this->contextMock->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->method('getResponse')->willReturn($this->responseMock);
        $this->contextMock->method('getObjectManager')
            ->willReturn($this->objectManagerMock);
        $this->contextMock->method('getEventManager')->willReturn($this->eventManagerMock);
        $this->contextMock->method('getView')->willReturn($this->viewMock);
        $this->contextMock->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);

        $this->categoryMock = $this->createMock(\Magento\Catalog\Model\Category::class);
        $this->categoryRepositoryMock = $this->getMockForAbstractClass(CategoryRepositoryInterface::class);

        $this->storeMock = $this->createMock(Store::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->storeManagerMock->method('getStore')->willReturn($this->storeMock);

        $this->catalogDesignMock = $this->createMock(Design::class);

        $resultPageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultPageFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->pageMock);

        $this->action = new View(
            $this->contextMock,
            $this->catalogDesignMock,
            $this->createMock(Session::class),
            $this->createMock(Registry::class),
            $this->storeManagerMock,
            $this->createMock(CategoryUrlPathGenerator::class),
            $resultPageFactoryMock,
            $this->createMock(ForwardFactory::class),
            $this->createMock(Resolver::class),
            $this->categoryRepositoryMock,
            $this->createMock(ToolbarMemorizer::class),
            $this->createMock(LayoutUpdateManager::class),
            $this->categoryHelperMock,
            $this->getMockForAbstractClass(LoggerInterface::class)
        );
    }

    /**
     * Apply custom layout update is correct
     *
     * @dataProvider getInvocationData
     * @param array $expectedData
     *
     * @return void
     */
    public function testApplyCustomLayoutUpdate(array $expectedData): void
    {
        $categoryId = 123;
        $pageLayout = 'page_layout';

        $this->requestMock->method('getParam')->willReturnMap(
            [
                [Action::PARAM_NAME_URL_ENCODED],
                ['id', false, $categoryId]
            ]
        );

        $this->categoryRepositoryMock->method('get')->with($categoryId)
            ->willReturn($this->categoryMock);

        $this->categoryHelperMock->expects($this->once())
            ->method('canShow')
            ->with($this->categoryMock)
            ->willReturn(true);

        $settings = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getPageLayout', 'getLayoutUpdates'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->categoryMock->expects($this->at(1))
            ->method('hasChildren')
            ->willReturn(true);
        $this->categoryMock->expects($this->at(2))
            ->method('hasChildren')
            ->willReturn($expectedData[1][0]['type'] === 'default');
        $this->categoryMock->expects($this->once())
            ->method('getDisplayMode')
            ->willReturn($expectedData[2][0]['displaymode']);
        $this->expectationForPageLayoutHandles($expectedData);
        $settings->expects($this->atLeastOnce())->method('getPageLayout')->willReturn($pageLayout);
        $settings->expects($this->once())->method('getLayoutUpdates')->willReturn(['update1', 'update2']);
        $this->catalogDesignMock->method('getDesignSettings')->willReturn($settings);

        $this->action->execute();
    }

    /**
     * Expected invocation for Layout Handles
     *
     * @param array $data
     *
     * @return void
     */
    private function expectationForPageLayoutHandles($data): void
    {
        $index = 1;

        foreach ($data as $expectedData) {
            $this->pageMock->expects($this->at($index))
                ->method('addPageLayoutHandles')
                ->with($expectedData[0], $expectedData[1], $expectedData[2]);
            $index++;
        }
    }

    /**
     * Data provider for execute method.
     *
     * @return array
     */
    public function getInvocationData(): array
    {
        return [
            [
                'layoutHandles' => [
                    [['type' => 'default'], null, false],
                    [['type' => 'default_without_children'], null, false],
                    [['displaymode' => 'products'], null, false]
                ]
            ],
            [
                'layoutHandles' => [
                    [['type' => 'default'], null, false],
                    [['type' => 'default_without_children'], null, false],
                    [['displaymode' => 'page'], null, false]
                ]
            ],
            [
                'layoutHandles' => [
                    [['type' => 'default'], null, false],
                    [['type' => 'default'], null, false],
                    [['displaymode' => 'poducts_and_page'], null, false]
                ]
            ]
        ];
    }
}
