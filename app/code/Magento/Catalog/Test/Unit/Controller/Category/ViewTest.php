<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Category;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Controller\Category\View;
use Magento\Catalog\Helper\Category as CategoryHelper;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Design;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Layout\ProcessorInterface;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ViewTest extends TestCase
{
    /**
     * @var MockObject|RequestInterface
     */
    private $requestMock;

    /**
     * @var MockObject|ResponseInterface
     */
    private $responseMock;

    /**
     * @var MockObject|CategoryHelper
     */
    private $categoryHelperMock;

    /**
     * @var MockObject|EventManagerInterface
     */
    private $eventManagerMock;

    /**
     * @var MockObject|Layout
     */
    private $layoutMock;

    /**
     * @var MockObject|ProcessorInterface
     */
    private $processorMock;

    /**
     * @var MockObject|ViewInterface
     */
    private $viewMock;

    /**
     * @var MockObject|Category
     */
    private $categoryMock;

    /**
     * @var MockObject|CategoryRepositoryInterface
     */
    private $categoryRepositoryMock;

    /**
     * @var MockObject|Store
     */
    private $storeMock;

    /**
     * @var MockObject|StoreManagerInterface
     */
    private $storeManagerMock;

    /**
     * @var MockObject|Design
     */
    private $catalogDesignMock;

    /**
     * @var MockObject|ResultFactory
     */
    private $resultFactoryMock;

    /**
     * @var MockObject|Page
     */
    private $pageMock;

    /**
     * @var MockObject|Config
     */
    private $pageConfigMock;

    /**
     * @var View
     */
    private $action;

    /**
     * Set up instances and mock objects
     */
    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->responseMock = $this->createMock(ResponseInterface::class);

        $this->categoryHelperMock = $this->createMock(CategoryHelper::class);
        $this->eventManagerMock = $this->createMock(EventManagerInterface::class);

        $this->processorMock = $this->createMock(ProcessorInterface::class);
        $this->layoutMock = $this->createMock(Layout::class);
        $this->layoutMock->expects($this->any())->method('getUpdate')->will($this->returnValue($this->processorMock));

        $this->pageConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()->getMock();
        $this->pageConfigMock->expects($this->any())->method('addBodyClass')->will($this->returnSelf());

        $this->pageMock = $this->getMockBuilder(Page::class)
            ->setMethods(['getConfig', 'initLayout', 'addPageLayoutHandles', 'getLayout', 'addUpdate'])
            ->disableOriginalConstructor()->getMock();
        $this->pageMock->expects($this->any())->method('getConfig')->will($this->returnValue($this->pageConfigMock));
        $this->pageMock->expects($this->any())->method('addPageLayoutHandles')->will($this->returnSelf());
        $this->pageMock->expects($this->any())->method('getLayout')->will($this->returnValue($this->layoutMock));
        $this->pageMock->expects($this->any())->method('addUpdate')->willReturnSelf();

        $this->viewMock = $this->createMock(ViewInterface::class);
        $this->viewMock->expects($this->any())->method('getLayout')->will($this->returnValue($this->layoutMock));

        $this->resultFactoryMock = $this->createMock(ResultFactory::class);
        $this->resultFactoryMock->expects($this->any())->method('create')->will($this->returnValue($this->pageMock));

        $this->categoryMock = $this->createMock(Category::class);
        $this->categoryRepositoryMock = $this->createMock(CategoryRepositoryInterface::class);

        $this->storeMock = $this->createMock(Store::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->storeManagerMock->expects($this->any())->method('getStore')->will($this->returnValue($this->storeMock));

        $this->catalogDesignMock = $this->createMock(Design::class);

        $resultPageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultPageFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->will($this->returnValue($this->pageMock));

        $this->action = (new ObjectManager($this))->getObject(
            View::class,
            [
                'request' => $this->requestMock,
                'catalogDesign' => $this->catalogDesignMock,
                'categoryRepository' => $this->categoryRepositoryMock,
                'storeManager' => $this->storeManagerMock,
                'resultPageFactory' => $resultPageFactoryMock,
                'categoryHelper' => $this->categoryHelperMock
            ]
        );
    }

    /**
     * Apply custom layout update is correct
     *
     * @dataProvider getInvocationData
     * @return void
     */
    public function testApplyCustomLayoutUpdate(array $expectedData): void
    {
        $categoryId = 123;
        $pageLayout = 'page_layout';

        $this->requestMock->expects($this->any())->method('getParam')->willReturnMap(
            [
                [ActionInterface::PARAM_NAME_URL_ENCODED],
                ['id', false, $categoryId]
            ]
        );

        $this->categoryRepositoryMock->expects($this->any())->method('get')->with($categoryId)
            ->will($this->returnValue($this->categoryMock));

        $this->categoryHelperMock->expects($this->once())
            ->method('canShow')
            ->with($this->categoryMock)
            ->willReturn(true);

        $settings = $this->createPartialMock(
            DataObject::class,
            ['getPageLayout', 'getLayoutUpdates']
        );
        $this->categoryMock->expects($this->at(1))
            ->method('hasChildren')
            ->willReturn(true);
        $this->categoryMock->expects($this->at(2))
            ->method('hasChildren')
            ->willReturn($expectedData[1][0]['type'] === 'default' ? true : false);
        $this->categoryMock->expects($this->once())
            ->method('getDisplayMode')
            ->willReturn($expectedData[2][0]['displaymode']);
        $this->expectationForPageLayoutHandles($expectedData);
        $settings->expects($this->atLeastOnce())
            ->method('getPageLayout')
            ->will($this->returnValue($pageLayout));
        $settings->expects($this->once())
            ->method('getLayoutUpdates')
            ->willReturn(['update1', 'update2']);
        $this->catalogDesignMock->expects($this->any())
            ->method('getDesignSettings')
            ->will($this->returnValue($settings));

        $this->action->execute();
    }

    /**
     * Expected invocation for Layout Handles
     *
     * @param array $data
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
