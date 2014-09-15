<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Controller\Category;

use Magento\Framework\App\Action\Action;
use Magento\TestFramework\Helper\ObjectManager;

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
     * @var \Magento\Framework\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
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
     * @var \Magento\Catalog\Model\CategoryFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryFactory;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $store;

    /**
     * @var \Magento\Framework\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\Design|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $catalogDesign;

    /**
     * @var \Magento\Theme\Helper\Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutHelper;

    /**
     * @var \Magento\Catalog\Controller\Category
     */
    protected $action;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $pageConfig;

    public function setUp()
    {
        $this->request = $this->getMock('Magento\Framework\App\RequestInterface');
        $this->response = $this->getMock('Magento\Framework\App\ResponseInterface');

        $this->categoryHelper = $this->getMock('Magento\Catalog\Helper\Category', [], [], '', false);
        $this->objectManager = $this->getMock('Magento\Framework\ObjectManager', [], [], '', false);
        $this->eventManager = $this->getMock('Magento\Framework\Event\ManagerInterface');

        $this->update = $this->getMock('Magento\Framework\View\Layout\ProcessorInterface');
        $this->layout = $this->getMock('Magento\Framework\View\Layout', [], [], '', false);
        $this->layout->expects($this->any())->method('getUpdate')->will($this->returnValue($this->update));

        $this->pageConfig = $this->getMockBuilder('Magento\Framework\View\Page\Config')
            ->disableOriginalConstructor()->getMock();
        $this->pageConfig->expects($this->any())->method('addBodyClass')->will($this->returnSelf());

        $this->page = $this->getMockBuilder('Magento\Framework\View\Page')
            ->setMethods(['getConfig', 'initLayout'])->disableOriginalConstructor()->getMock();
        $this->page->expects($this->any())->method('getConfig')->will($this->returnValue($this->pageConfig));

        $this->view = $this->getMock('Magento\Framework\App\ViewInterface');
        $this->view->expects($this->any())->method('getLayout')->will($this->returnValue($this->layout));
        $this->view->expects($this->any())->method('getPage')->will($this->returnValue($this->page));

        $this->context = $this->getMock('Magento\Backend\App\Action\Context', [], [], '', false);
        $this->context->expects($this->any())->method('getRequest')->will($this->returnValue($this->request));
        $this->context->expects($this->any())->method('getResponse')->will($this->returnValue($this->response));
        $this->context->expects($this->any())->method('getObjectManager')
            ->will($this->returnValue($this->objectManager));
        $this->context->expects($this->any())->method('getEventManager')->will($this->returnValue($this->eventManager));
        $this->context->expects($this->any())->method('getView')->will($this->returnValue($this->view));

        $this->category = $this->getMock('Magento\Catalog\Model\Category', [], [], '', false);
        $this->categoryFactory = $this->getMock('Magento\Catalog\Model\CategoryFactory', ['create'], [], '', false);

        $this->store = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $this->storeManager = $this->getMock('Magento\Framework\StoreManagerInterface');
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($this->store));

        $this->catalogDesign = $this->getMock('Magento\Catalog\Model\Design', [], [], '', false);
        $this->layoutHelper = $this->getMock('Magento\Theme\Helper\Layout', [], [], '', false);

        $this->action = (new ObjectManager($this))->getObject('Magento\Catalog\Controller\Category\View', [
            'context' => $this->context,
            'catalogDesign' => $this->catalogDesign,
            'categoryFactory' => $this->categoryFactory,
            'storeManager' => $this->storeManager,
        ]);
    }

    public function testApplyCustomLayoutUpdate()
    {
        $categoryId = 123;
        $pageLayout = 'page_layout';

        $this->objectManager->expects($this->any())->method('get')->will($this->returnValueMap([
            ['Magento\Catalog\Helper\Category', $this->categoryHelper],
            ['Magento\Theme\Helper\Layout', $this->layoutHelper],
        ]));

        $this->request->expects($this->any())->method('getParam')->will($this->returnValueMap([
            [Action::PARAM_NAME_URL_ENCODED],
            ['id', false, $categoryId],
        ]));

        $this->categoryFactory->expects($this->any())->method('create')->will($this->returnValue($this->category));
        $this->category->expects($this->any())->method('setStoreId')->will($this->returnSelf());
        $this->category->expects($this->any())->method('load')->with($categoryId)->will($this->returnSelf());

        $this->categoryHelper->expects($this->any())->method('canShow')->will($this->returnValue(true));

        $settings = $this->getMock('Magento\Framework\Object', ['getPageLayout'], [], '', false);
        $settings->expects($this->atLeastOnce())->method('getPageLayout')->will($this->returnValue($pageLayout));

        $this->catalogDesign->expects($this->any())->method('getDesignSettings')->will($this->returnValue($settings));

        $this->action->execute();
    }
}
