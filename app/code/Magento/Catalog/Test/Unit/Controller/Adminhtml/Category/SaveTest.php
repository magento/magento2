<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Category;

/**
 * Class SaveTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRawFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultJsonFactoryMock;

    /**
     * @var \Magento\Framework\View\LayoutFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutFactoryMock;

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
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Catalog\Controller\Adminhtml\Category\Save
     */
    protected $save;

    /**
     * Set up
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->markTestSkipped('Due to MAGETWO-48956');
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->contextMock = $this->getMock(
            'Magento\Backend\App\Action\Context',
            [
                'getTitle',
                'getRequest',
                'getObjectManager',
                'getEventManager',
                'getResponse',
                'getMessageManager',
                'getResultRedirectFactory'
            ],
            [],
            '',
            false
        );
        $this->resultRedirectFactoryMock = $this->getMock(
            'Magento\Backend\Model\View\Result\RedirectFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->resultRawFactoryMock = $this->getMock(
            'Magento\Framework\Controller\Result\RawFactory',
            [],
            [],
            '',
            false
        );
        $this->resultJsonFactoryMock = $this->getMock(
            'Magento\Framework\Controller\Result\JsonFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->layoutFactoryMock = $this->getMock(
            'Magento\Framework\View\LayoutFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->requestMock = $this->getMockForAbstractClass(
            'Magento\Framework\App\RequestInterface',
            [],
            '',
            false,
            true,
            true,
            ['getParam', 'getPost', 'getPostValue']
        );
        $this->objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventManagerMock = $this->getMockForAbstractClass(
            'Magento\Framework\Event\ManagerInterface',
            [],
            '',
            false,
            true,
            true,
            ['dispatch']
        );
        $this->responseMock = $this->getMockForAbstractClass(
            'Magento\Framework\App\ResponseInterface',
            [],
            '',
            false
        );
        $this->messageManagerMock = $this->getMockForAbstractClass(
            'Magento\Framework\Message\ManagerInterface',
            [],
            '',
            false,
            true,
            true,
            ['addSuccess', 'getMessages']
        );

        $this->contextMock->expects($this->any())->method('getTitle')->willReturn($this->titleMock);
        $this->contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())->method('getObjectManager')->willReturn($this->objectManagerMock);
        $this->contextMock->expects($this->any())->method('getEventManager')->willReturn($this->eventManagerMock);
        $this->contextMock->expects($this->any())->method('getResponse')->willReturn($this->responseMock);
        $this->contextMock->expects($this->any())->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);

        $this->save = $this->objectManager->getObject(
            'Magento\Catalog\Controller\Adminhtml\Category\Save',
            [
                'context' => $this->contextMock,
                'resultRawFactory' => $this->resultRawFactoryMock,
                'resultJsonFactory' => $this->resultJsonFactoryMock,
                'layoutFactory' => $this->layoutFactoryMock
            ]
        );
    }

    /**
     * Run test execute method
     *
     * @param int|bool $categoryId
     * @param int $storeId
     * @param int|null $parentId
     * @return void
     *
     * @dataProvider dataProviderExecute
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecute($categoryId, $storeId, $parentId)
    {
        $rootCategoryId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
        $products = [['any_product']];
        $postData = [
            'general-data',
            'parent' => $parentId,
            'category_products' => json_encode($products),
        ];

        if (isset($storeId)) {
            $postData['store_id'] = $storeId;
        }
        /**
         * @var \Magento\Backend\Model\View\Result\Redirect
         * |\PHPUnit_Framework_MockObject_MockObject $resultRedirectMock
         */
        $resultRedirectMock = $this->getMock(
            'Magento\Backend\Model\View\Result\Redirect',
            [],
            [],
            '',
            false
        );
        /**
         * @var \Magento\Framework\View\Element\Messages
         * |\PHPUnit_Framework_MockObject_MockObject $blockMock
         */
        $blockMock = $this->getMock(
            'Magento\Framework\View\Element\Messages',
            ['setMessages', 'getGroupedHtml'],
            [],
            '',
            false
        );
        /**
         * @var \Magento\Catalog\Model\Category
         * |\PHPUnit_Framework_MockObject_MockObject $categoryMock
         */
        $categoryMock = $this->getMock(
            'Magento\Catalog\Model\Category',
            [
                'setStoreId',
                'load',
                'getPath',
                'getResource',
                'setPath',
                'setParentId',
                'setData',
                'addData',
                'setAttributeSetId',
                'getDefaultAttributeSetId',
                'getProductsReadonly',
                'setPostedProducts',
                'getId',
                'validate',
                'unsetData',
                'save',
                'toArray'
            ],
            [],
            '',
            false
        );
        /**
         * @var \Magento\Catalog\Model\Category
         * |\PHPUnit_Framework_MockObject_MockObject $parentCategoryMock
         */
        $parentCategoryMock = $this->getMock(
            'Magento\Catalog\Model\Category',
            [
                'setStoreId',
                'load',
                'getPath',
                'setPath',
                'setParentId',
                'setData',
                'addData',
                'setAttributeSetId',
                'getDefaultAttributeSetId',
                'getProductsReadonly',
                'setPostedProducts',
                'getId'
            ],
            [],
            '',
            false
        );
        /**
         * @var \Magento\Backend\Model\Auth\Session
         * |\PHPUnit_Framework_MockObject_MockObject $sessionMock
         */
        $sessionMock = $this->getMock(
            'Magento\Backend\Model\Auth\Session',
            [],
            [],
            '',
            false
        );
        /**
         * @var \Magento\Framework\Registry
         * |\PHPUnit_Framework_MockObject_MockObject $registryMock
         */
        $registryMock = $this->getMock(
            'Magento\Framework\Registry',
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
            'Magento\Cms\Model\Wysiwyg\Config',
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
            'Magento\Store\Model\StoreManagerInterface',
            [],
            '',
            false,
            true,
            true,
            ['getStore', 'getRootCategoryId']
        );
        /**
         * @var \Magento\Framework\View\Layout
         * |\PHPUnit_Framework_MockObject_MockObject $layoutMock
         */
        $layoutMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\Layout',
            [],
            '',
            false,
            true,
            true,
            ['getMessagesBlock']
        );
        /**
         * @var \Magento\Framework\Controller\Result\Json
         * |\PHPUnit_Framework_MockObject_MockObject $resultJsonMock
         */
        $resultJsonMock = $this->getMock(
            'Magento\Cms\Model\Wysiwyg\Config',
            ['setData'],
            [],
            '',
            false
        );
        /**
         * @var \Magento\Framework\Message\Collection
         * |\PHPUnit_Framework_MockObject_MockObject $messagesMock
         */
        $messagesMock = $this->getMock(
            'Magento\Framework\Message\Collection',
            [],
            [],
            '',
            false
        );

        $messagesMock->expects($this->once())
            ->method('getCountByType')
            ->will($this->returnValue(0));

        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($resultRedirectMock));
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->will(
                $this->returnValueMap(
                    [
                        ['id', false, $categoryId],
                        ['store', null, $storeId],
                        ['parent', null, $parentId],
                    ]
                )
            );
        $this->objectManagerMock->expects($this->atLeastOnce())
            ->method('create')
            ->will($this->returnValue($categoryMock));
        $this->objectManagerMock->expects($this->atLeastOnce())
            ->method('get')
            ->will(
                $this->returnValueMap(
                    [
                        ['Magento\Backend\Model\Auth\Session', $sessionMock],
                        ['Magento\Framework\Registry', $registryMock],
                        ['Magento\Cms\Model\Wysiwyg\Config', $wysiwygConfigMock],
                        ['Magento\Store\Model\StoreManagerInterface', $storeManagerMock],
                    ]
                )
            );
        $categoryMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId);
        $registryMock->expects($this->any())
            ->method('register')
            ->will(
                $this->returnValueMap(
                    [
                        ['category', $categoryMock],
                        ['current_category', $categoryMock],
                    ]
                )
            );
        $wysiwygConfigMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId);
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getPost')
            ->will(
                $this->returnValueMap(
                    [
                        ['use_config', ['attribute']],
                        ['use_default', ['default-attribute']],
                        ['return_session_messages_only', true],
                    ]
                )
            );
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getPostValue')
            ->willReturn($postData);
        $addData = $postData;
        $addData['image'] = ['delete' => true];
        $categoryMock->expects($this->once())
            ->method('addData')
            ->with($addData);
        $categoryMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($categoryId));

        if (!$parentId) {
            if ($storeId) {
                $storeManagerMock->expects($this->once())
                    ->method('getStore')
                    ->with($storeId)
                    ->will($this->returnSelf());
                $storeManagerMock->expects($this->once())
                    ->method('getRootCategoryId')
                    ->will($this->returnValue($rootCategoryId));
                $parentId = $rootCategoryId;
            }
        }
        $categoryMock->expects($this->any())
            ->method('load')
            ->will($this->returnValue($parentCategoryMock));
        $parentCategoryMock->expects($this->once())
            ->method('getPath')
            ->will($this->returnValue('parent_category_path'));
        $parentCategoryMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($parentId));
        $categoryMock->expects($this->once())
            ->method('setPath')
            ->with('parent_category_path');
        $categoryMock->expects($this->once())
            ->method('setParentId')
            ->with($parentId);
        $categoryMock->expects($this->atLeastOnce())
            ->method('setData')
            ->will(
                $this->returnValueMap(
                    [
                        ['attribute', null, true],
                        ['default-attribute', false, true],
                        ['use_post_data_config', ['attribute'], true],
                    ]
                )
            );
        $categoryMock->expects($this->once())
            ->method('getDefaultAttributeSetId')
            ->will($this->returnValue('default-attribute'));
        $categoryMock->expects($this->once())
            ->method('setAttributeSetId')
            ->with('default-attribute');
        $categoryMock->expects($this->once())
            ->method('getProductsReadonly')
            ->will($this->returnValue(false));
        $categoryMock->expects($this->once())
            ->method('setPostedProducts')
            ->with($products);
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with(
                'catalog_category_prepare_save',
                ['category' => $categoryMock, 'request' => $this->requestMock]
            );

        $categoryResource = $this->getMock(
            'Magento\Catalog\Model\ResourceModel\Category',
            [],
            [],
            '',
            false
        );
        $categoryMock->expects($this->once())
            ->method('getResource')
            ->will($this->returnValue($categoryResource));
        $categoryMock->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(true));
        $categoryMock->expects($this->once())
            ->method('unsetData')
            ->with('use_post_data_config');
        $categoryMock->expects($this->once())
            ->method('save');
        $this->messageManagerMock->expects($this->once())
            ->method('addSuccess')
            ->with(__('You saved the category.'));
        $categoryMock->expects($this->at(1))
            ->method('getId')
            ->will($this->returnValue(111));
        $this->layoutFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($layoutMock));
        $layoutMock->expects($this->once())
            ->method('getMessagesBlock')
            ->will($this->returnValue($blockMock));
        $this->messageManagerMock->expects($this->any())
            ->method('getMessages')
            ->will($this->returnValue($messagesMock));
        $blockMock->expects($this->once())
            ->method('setMessages')
            ->with($messagesMock);
        $blockMock->expects($this->once())
            ->method('getGroupedHtml')
            ->will($this->returnValue('grouped-html'));
        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($resultJsonMock));
        $categoryMock->expects($this->once())
            ->method('toArray')
            ->will($this->returnValue(['category-data']));
        $resultJsonMock->expects($this->once())
            ->method('setData')
            ->with(
                [
                    'messages' => 'grouped-html',
                    'error' => false,
                    'category' => ['category-data'],
                ]
            )
            ->will($this->returnValue('result-execute'));

        $this->assertEquals('result-execute', $this->save->execute());
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
                'categoryId' => false,
                'storeId' => 7,
                'parentId' => 123,
            ],
            [
                'categoryId' => false,
                'storeId' => 7,
                'parentId' => null,
            ]
        ];
    }
}
