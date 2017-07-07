<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Category;

/**
 * Class EditTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageMock;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultJsonFactoryMock;

    /**
     * @var \Magento\Framework\View\LayoutFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerInterfaceMock;

    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\View\Page\Title|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $titleMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Catalog\Controller\Adminhtml\Category\Edit
     */
    protected $edit;

    /**
     * @var \Magento\Backend\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var \Magento\Catalog\Model\Category|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryMock;

    /**
     * Set up
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->categoryMock = $this->getMock(
            \Magento\Catalog\Model\Category::class,
            [
                'getPath',
                'addData',
                'getId',
                'getName',
                'getResource',
                'setStoreId',
                'toArray'
            ],
            [],
            '',
            false
        );

        $this->contextMock = $this->getMock(
            \Magento\Backend\App\Action\Context::class,
            [
                'getTitle',
                'getRequest',
                'getObjectManager',
                'getEventManager',
                'getResponse',
                'getMessageManager',
                'getResultRedirectFactory',
                'getSession'
            ],
            [],
            '',
            false
        );
        $this->resultRedirectFactoryMock = $this->getMock(
            \Magento\Backend\Model\View\Result\RedirectFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $this->resultPageMock = $this->getMock(
            \Magento\Framework\View\Result\Page::class,
            ['setActiveMenu', 'getConfig', 'addBreadcrumb', 'getLayout', 'getBlock', 'getTitle', 'prepend'],
            [],
            '',
            false
        );
        $this->resultPageMock->expects($this->any())
            ->method('getConfig')
            ->will($this->returnSelf());
        $this->resultPageMock->expects($this->any())
            ->method('getTitle')
            ->will($this->returnSelf());

        $this->resultPageFactoryMock = $this->getMock(
            \Magento\Framework\View\Result\PageFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->resultPageFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->resultPageMock);

        $this->resultJsonFactoryMock = $this->getMock(
            \Magento\Framework\Controller\Result\JsonFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->storeManagerInterfaceMock = $this->getMockForAbstractClass(
            \Magento\Store\Model\StoreManagerInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getStore', 'getDefaultStoreView', 'getRootCategoryId', 'getCode']
        );
        $this->requestMock = $this->getMockForAbstractClass(
            \Magento\Framework\App\RequestInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getParam', 'getPost', 'getPostValue', 'getQuery', 'setParam']
        );
        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventManagerMock = $this->getMockForAbstractClass(
            \Magento\Framework\Event\ManagerInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['dispatch']
        );
        $this->sessionMock = $this->getMock(
            \Magento\Backend\Model\Session::class,
            ['__call'],
            [],
            '',
            false
        );

        $this->contextMock->expects($this->any())->method('getTitle')->willReturn($this->titleMock);
        $this->contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())->method('getObjectManager')->willReturn($this->objectManagerMock);
        $this->contextMock->expects($this->any())->method('getEventManager')->willReturn($this->eventManagerMock);
        $this->contextMock->expects($this->any())->method('getResponse')->willReturn($this->responseMock);
        $this->contextMock->expects($this->any())->method('getSession')->willReturn($this->sessionMock);
        $this->contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);

        $this->edit = $this->objectManager->getObject(
            \Magento\Catalog\Controller\Adminhtml\Category\Edit::class,
            [
                'context' => $this->contextMock,
                'resultPageFactory' => $this->resultPageFactoryMock,
                'resultJsonFactory' => $this->resultJsonFactoryMock,
                'storeManager' => $this->storeManagerInterfaceMock
            ]
        );
    }

    /**
     * Run test execute method
     *
     * @param int|bool $categoryId
     * @param int $storeId
     * @return void
     *
     * @dataProvider dataProviderExecute
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecute($categoryId, $storeId)
    {
        $rootCategoryId = 2;

        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->will(
                $this->returnValueMap(
                    [
                        ['id', false, $categoryId],
                        ['store', null, $storeId],
                    ]
                )
            );
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getQuery')
            ->with('isAjax')
            ->will($this->returnValue(false));

        $this->mockInitCategoryCall();

        $this->sessionMock->expects($this->once())
            ->method('__call')
            ->will($this->returnValue([]));

        $this->storeManagerInterfaceMock->expects($this->any())
            ->method('getStore')
            ->with($storeId)
            ->will($this->returnSelf());

        if (!$categoryId) {
            if (!$storeId) {
                $this->storeManagerInterfaceMock->expects($this->once())
                    ->method('getDefaultStoreView')
                    ->will($this->returnSelf());
            }
            $this->storeManagerInterfaceMock->expects($this->once())
                ->method('getRootCategoryId')
                ->will($this->returnValue($rootCategoryId));
            $categoryId = $rootCategoryId;
        }

        $this->requestMock->expects($this->atLeastOnce())
            ->method('setParam')
            ->with('id', $categoryId)
            ->will($this->returnValue(true));

        $this->categoryMock->expects($this->atLeastOnce())
            ->method('getId')
            ->will($this->returnValue($categoryId));

        /**
         * @var \Magento\Framework\View\Element\Template
         * |\PHPUnit_Framework_MockObject_MockObject $blockMock
         */
        $blockMock = $this->getMock(
            \Magento\Framework\View\Element\Template::class,
            ['setStoreId'],
            [],
            '',
            false
        );
        $blockMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId);

        $this->resultPageMock->expects($this->once())
            ->method('getLayout')
            ->will($this->returnSelf());

        $this->resultPageMock->expects($this->once())
            ->method('getBlock')
            ->willReturn($blockMock);

        $this->edit->execute();
    }

    /**
     * Data provider for execute
     *
     * @return array
     */
    public function dataProviderExecute()
    {
        return [
            [
                'categoryId' => null,
                'storeId' => null,
            ],
            [
                'categoryId' => null,
                'storeId' => 7,
            ]
        ];
    }

    /**
     * Mock for method "_initCategory"
     */
    private function mockInitCategoryCall()
    {
        /**
         * @var \Magento\Framework\Registry
         * |\PHPUnit_Framework_MockObject_MockObject $registryMock
         */
        $registryMock = $this->getMock(
            \Magento\Framework\Registry::class,
            ['register'],
            [],
            '',
            false
        );
        /**
         * @var \Magento\Cms\Model\Wysiwyg\Config
         * |\PHPUnit_Framework_MockObject_MockObject $wysiwygConfigMock
         */
        $wysiwygConfigMock = $this->getMock(
            \Magento\Cms\Model\Wysiwyg\Config::class,
            ['setStoreId'],
            [],
            '',
            false
        );
        /**
         * @var \Magento\Store\Model\StoreManagerInterface
         * |\PHPUnit_Framework_MockObject_MockObject $storeManagerMock
         */
        $storeManagerMock = $this->getMockForAbstractClass(
            \Magento\Store\Model\StoreManagerInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getStore', 'getRootCategoryId']
        );

        $this->objectManagerMock->expects($this->atLeastOnce())
            ->method('create')
            ->will($this->returnValue($this->categoryMock));

        $this->objectManagerMock->expects($this->atLeastOnce())
            ->method('get')
            ->will(
                $this->returnValueMap(
                    [
                        [\Magento\Framework\Registry::class, $registryMock],
                        [\Magento\Cms\Model\Wysiwyg\Config::class, $wysiwygConfigMock],
                        [\Magento\Store\Model\StoreManagerInterface::class, $storeManagerMock],
                    ]
                )
            );
    }
}
