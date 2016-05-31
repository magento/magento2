<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Category;

use Magento\Framework\App\Action\Action;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $response;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryHelper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\View\Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layout;

    /**
     * @var \Magento\Framework\View\Layout\ProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $update;

    /**
     * @var \Magento\Framework\App\ViewInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $view;

    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Catalog\Model\Category|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $category;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $store;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\Design|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $catalogDesign;

    /**
     * @var \Magento\Catalog\Controller\Category\View
     */
    protected $action;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultFactory;

    /**
     * @var \Magento\Framework\View\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $page;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $pageConfig;

    /**
     * Set up instances and mock objects
     */
    protected function setUp()
    {
        $this->request = $this->getMock('Magento\Framework\App\RequestInterface');
        $this->response = $this->getMock('Magento\Framework\App\ResponseInterface');

        $this->categoryHelper = $this->getMock('Magento\Catalog\Helper\Category', [], [], '', false);
        $this->objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->eventManager = $this->getMock('Magento\Framework\Event\ManagerInterface');

        $this->update = $this->getMock('Magento\Framework\View\Layout\ProcessorInterface');
        $this->layout = $this->getMock('Magento\Framework\View\Layout', [], [], '', false);
        $this->layout->expects($this->any())->method('getUpdate')->will($this->returnValue($this->update));

        $this->pageConfig = $this->getMockBuilder('Magento\Framework\View\Page\Config')
            ->disableOriginalConstructor()->getMock();
        $this->pageConfig->expects($this->any())->method('addBodyClass')->will($this->returnSelf());

        $this->page = $this->getMockBuilder('Magento\Framework\View\Page')
            ->setMethods(['getConfig', 'initLayout', 'addPageLayoutHandles', 'getLayout', 'addUpdate'])
            ->disableOriginalConstructor()->getMock();
        $this->page->expects($this->any())->method('getConfig')->will($this->returnValue($this->pageConfig));
        $this->page->expects($this->any())->method('addPageLayoutHandles')->will($this->returnSelf());
        $this->page->expects($this->any())->method('getLayout')->will($this->returnValue($this->layout));
        $this->page->expects($this->any())->method('addUpdate')->willReturnSelf();

        $this->view = $this->getMock('Magento\Framework\App\ViewInterface');
        $this->view->expects($this->any())->method('getLayout')->will($this->returnValue($this->layout));

        $this->resultFactory = $this->getMock('Magento\Framework\Controller\ResultFactory', [], [], '', false);
        $this->resultFactory->expects($this->any())->method('create')->will($this->returnValue($this->page));

        $this->context = $this->getMock('Magento\Backend\App\Action\Context', [], [], '', false);
        $this->context->expects($this->any())->method('getRequest')->will($this->returnValue($this->request));
        $this->context->expects($this->any())->method('getResponse')->will($this->returnValue($this->response));
        $this->context->expects($this->any())->method('getObjectManager')
            ->will($this->returnValue($this->objectManager));
        $this->context->expects($this->any())->method('getEventManager')->will($this->returnValue($this->eventManager));
        $this->context->expects($this->any())->method('getView')->will($this->returnValue($this->view));
        $this->context->expects($this->any())->method('getResultFactory')
            ->will($this->returnValue($this->resultFactory));

        $this->category = $this->getMock('Magento\Catalog\Model\Category', [], [], '', false);
        $this->categoryRepository = $this->getMock('Magento\Catalog\Api\CategoryRepositoryInterface');

        $this->store = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $this->storeManager = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($this->store));

        $this->catalogDesign = $this->getMock('Magento\Catalog\Model\Design', [], [], '', false);

        $resultPageFactory = $this->getMockBuilder('Magento\Framework\View\Result\PageFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultPageFactory->expects($this->atLeastOnce())
            ->method('create')
            ->will($this->returnValue($this->page));

        $this->action = (new ObjectManager($this))->getObject('Magento\Catalog\Controller\Category\View', [
            'context' => $this->context,
            'catalogDesign' => $this->catalogDesign,
            'categoryRepository' => $this->categoryRepository,
            'storeManager' => $this->storeManager,
            'resultPageFactory' => $resultPageFactory
        ]);
    }

    public function testApplyCustomLayoutUpdate()
    {
        $categoryId = 123;
        $pageLayout = 'page_layout';

        $this->objectManager->expects($this->any())->method('get')->will($this->returnValueMap([
            ['Magento\Catalog\Helper\Category', $this->categoryHelper],
        ]));

        $this->request->expects($this->any())->method('getParam')->will($this->returnValueMap([
            [Action::PARAM_NAME_URL_ENCODED],
            ['id', false, $categoryId],
        ]));

        $this->categoryRepository->expects($this->any())->method('get')->with($categoryId)
            ->will($this->returnValue($this->category));

        $this->categoryHelper->expects($this->any())->method('canShow')->will($this->returnValue(true));

        $settings = $this->getMock(
            'Magento\Framework\DataObject',
            ['getPageLayout', 'getLayoutUpdates'],
            [],
            '',
            false
        );
        $settings->expects($this->atLeastOnce())->method('getPageLayout')->will($this->returnValue($pageLayout));
        $settings->expects($this->once())->method('getLayoutUpdates')->willReturn(['update1', 'update2']);

        $this->catalogDesign->expects($this->any())->method('getDesignSettings')->will($this->returnValue($settings));

        $this->action->execute();
    }
}
