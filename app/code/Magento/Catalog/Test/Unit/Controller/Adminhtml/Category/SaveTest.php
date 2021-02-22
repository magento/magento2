<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Category;

/**
 * Class SaveTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resultJsonFactoryMock;

    /**
     * @var \Magento\Framework\View\LayoutFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $layoutFactoryMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $objectManagerMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $eventManagerMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $messageManagerMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Catalog\Controller\Adminhtml\Category\Save
     */
    private $save;

    /**
     * Set up
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->resultRedirectFactoryMock = $this->createPartialMock(
            \Magento\Backend\Model\View\Result\RedirectFactory::class,
            ['create']
        );
        $this->resultJsonFactoryMock = $this->createPartialMock(
            \Magento\Framework\Controller\Result\JsonFactory::class,
            ['create']
        );
        $this->layoutFactoryMock = $this->createPartialMock(\Magento\Framework\View\LayoutFactory::class, ['create']);
        $this->requestMock = $this->getMockForAbstractClass(
            \Magento\Framework\App\RequestInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getParam', 'getPost', 'getPostValue']
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
        $this->messageManagerMock = $this->getMockForAbstractClass(
            \Magento\Framework\Message\ManagerInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['addSuccessMessage', 'getMessages']
        );

        $this->save = $this->objectManager->getObject(
            \Magento\Catalog\Controller\Adminhtml\Category\Save::class,
            [
                'request' => $this->requestMock,
                'eventManager' => $this->eventManagerMock,
                'messageManager' => $this->messageManagerMock,
                'resultJsonFactory' => $this->resultJsonFactoryMock,
                'layoutFactory' => $this->layoutFactoryMock,
                'resultRedirectFactory' => $this->resultRedirectFactoryMock
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
        $this->markTestSkipped('Due to MAGETWO-48956');

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
         * |\PHPUnit\Framework\MockObject\MockObject $resultRedirectMock
         */
        $resultRedirectMock = $this->createMock(\Magento\Backend\Model\View\Result\Redirect::class);
        /**
         * @var \Magento\Framework\View\Element\Messages
         * |\PHPUnit\Framework\MockObject\MockObject $blockMock
         */
        $blockMock = $this->createPartialMock(
            \Magento\Framework\View\Element\Messages::class,
            ['setMessages', 'getGroupedHtml']
        );
        /**
         * @var \Magento\Catalog\Model\Category
         * |\PHPUnit\Framework\MockObject\MockObject $categoryMock
         */
        $categoryMock = $this->createPartialMock(\Magento\Catalog\Model\Category::class, [
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
            ]);
        /**
         * @var \Magento\Catalog\Model\Category
         * |\PHPUnit\Framework\MockObject\MockObject $parentCategoryMock
         */
        $parentCategoryMock = $this->createPartialMock(\Magento\Catalog\Model\Category::class, [
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
            ]);
        /**
         * @var \Magento\Backend\Model\Auth\Session
         * |\PHPUnit\Framework\MockObject\MockObject $sessionMock
         */
        $sessionMock = $this->createMock(\Magento\Backend\Model\Auth\Session::class);
        /**
         * @var \Magento\Framework\Registry
         * |\PHPUnit\Framework\MockObject\MockObject $registryMock
         */
        $registryMock = $this->createPartialMock(\Magento\Framework\Registry::class, ['register']);
        /**
         * @var \Magento\Cms\Model\Wysiwyg\Config
         * |\PHPUnit\Framework\MockObject\MockObject $wysiwygConfigMock
         */
        $wysiwygConfigMock = $this->createPartialMock(\Magento\Cms\Model\Wysiwyg\Config::class, ['setStoreId']);
        /**
         * @var \Magento\Store\Model\StoreManagerInterface
         * |\PHPUnit\Framework\MockObject\MockObject $storeManagerMock
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
        /**
         * @var \Magento\Framework\View\Layout
         * |\PHPUnit\Framework\MockObject\MockObject $layoutMock
         */
        $layoutMock = $this->getMockForAbstractClass(
            \Magento\Framework\View\Layout::class,
            [],
            '',
            false,
            true,
            true,
            ['getMessagesBlock']
        );
        /**
         * @var \Magento\Framework\Controller\Result\Json
         * |\PHPUnit\Framework\MockObject\MockObject $resultJsonMock
         */
        $resultJsonMock = $this->createPartialMock(\Magento\Cms\Model\Wysiwyg\Config::class, ['setData']);
        /**
         * @var \Magento\Framework\Message\Collection
         * |\PHPUnit\Framework\MockObject\MockObject $messagesMock
         */
        $messagesMock = $this->createMock(\Magento\Framework\Message\Collection::class);

        $messagesMock->expects($this->once())
            ->method('getCountByType')
            ->willReturn(0);

        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirectMock);
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->willReturnMap(
                
                    [
                        ['id', false, $categoryId],
                        ['store', null, $storeId],
                        ['parent', null, $parentId],
                    ]
                
            );
        $this->objectManagerMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($categoryMock);
        $this->objectManagerMock->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnMap(
                
                    [
                        [\Magento\Backend\Model\Auth\Session::class, $sessionMock],
                        [\Magento\Framework\Registry::class, $registryMock],
                        [\Magento\Cms\Model\Wysiwyg\Config::class, $wysiwygConfigMock],
                        [\Magento\Store\Model\StoreManagerInterface::class, $storeManagerMock],
                    ]
                
            );
        $categoryMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId);
        $registryMock->expects($this->any())
            ->method('register')
            ->willReturnMap(
                
                    [
                        ['category', $categoryMock],
                        ['current_category', $categoryMock],
                    ]
                
            );
        $wysiwygConfigMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId);
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getPost')
            ->willReturnMap(
                
                    [
                        ['use_config', ['attribute']],
                        ['use_default', ['default-attribute']],
                        ['return_session_messages_only', true],
                    ]
                
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
            ->willReturn($categoryId);

        if (!$parentId) {
            if ($storeId) {
                $storeManagerMock->expects($this->once())
                    ->method('getStore')
                    ->with($storeId)
                    ->willReturnSelf();
                $storeManagerMock->expects($this->once())
                    ->method('getRootCategoryId')
                    ->willReturn($rootCategoryId);
                $parentId = $rootCategoryId;
            }
        }
        $categoryMock->expects($this->any())
            ->method('load')
            ->willReturn($parentCategoryMock);
        $parentCategoryMock->expects($this->once())
            ->method('getPath')
            ->willReturn('parent_category_path');
        $parentCategoryMock->expects($this->once())
            ->method('getId')
            ->willReturn($parentId);
        $categoryMock->expects($this->once())
            ->method('setPath')
            ->with('parent_category_path');
        $categoryMock->expects($this->once())
            ->method('setParentId')
            ->with($parentId);
        $categoryMock->expects($this->atLeastOnce())
            ->method('setData')
            ->willReturnMap(
                
                    [
                        ['attribute', null, true],
                        ['default-attribute', false, true],
                        ['use_post_data_config', ['attribute'], true],
                    ]
                
            );
        $categoryMock->expects($this->once())
            ->method('getDefaultAttributeSetId')
            ->willReturn('default-attribute');
        $categoryMock->expects($this->once())
            ->method('setAttributeSetId')
            ->with('default-attribute');
        $categoryMock->expects($this->once())
            ->method('getProductsReadonly')
            ->willReturn(false);
        $categoryMock->expects($this->once())
            ->method('setPostedProducts')
            ->with($products);
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with(
                'catalog_category_prepare_save',
                ['category' => $categoryMock, 'request' => $this->requestMock]
            );

        $categoryResource = $this->createMock(\Magento\Catalog\Model\ResourceModel\Category::class);
        $categoryMock->expects($this->once())
            ->method('getResource')
            ->willReturn($categoryResource);
        $categoryMock->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $categoryMock->expects($this->once())
            ->method('unsetData')
            ->with('use_post_data_config');
        $categoryMock->expects($this->once())
            ->method('save');
        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('You saved the category.'));
        $categoryMock->expects($this->at(1))
            ->method('getId')
            ->willReturn(111);
        $this->layoutFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($layoutMock);
        $layoutMock->expects($this->once())
            ->method('getMessagesBlock')
            ->willReturn($blockMock);
        $this->messageManagerMock->expects($this->any())
            ->method('getMessages')
            ->willReturn($messagesMock);
        $blockMock->expects($this->once())
            ->method('setMessages')
            ->with($messagesMock);
        $blockMock->expects($this->once())
            ->method('getGroupedHtml')
            ->willReturn('grouped-html');
        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultJsonMock);
        $categoryMock->expects($this->once())
            ->method('toArray')
            ->willReturn(['category-data']);
        $resultJsonMock->expects($this->once())
            ->method('setData')
            ->with(
                [
                    'messages' => 'grouped-html',
                    'error' => false,
                    'category' => ['category-data'],
                ]
            )
            ->willReturn('result-execute');

        $categoryMock->expects($this->once())
            ->method('setStoreId')
            ->willReturn(1);
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

    /**
     * @return array
     */
    public function imagePreprocessingDataProvider()
    {
        $dataWithImage = [
            'image' => 'path.jpg',
            'name' => 'category',
            'description' => '',
            'parent' => 0
        ];
        $expectedSameAsDataWithImage = $dataWithImage;

        $dataWithoutImage = [
            'name' => 'category',
            'description' => '',
            'parent' => 0
        ];
        $expectedIfDataWithoutImage = $dataWithoutImage;
        $expectedIfDataWithoutImage['image'] = '';

        return [
            'categoryPostData contains image' => [$dataWithImage, $expectedSameAsDataWithImage],
            'categoryPostData doesn\'t contain image' => [$dataWithoutImage, $expectedIfDataWithoutImage],
        ];
    }

    /**
     * @dataProvider imagePreprocessingDataProvider
     *
     * @param array $data
     * @param array $expected
     */
    public function testImagePreprocessing($data, $expected)
    {
        $eavConfig = $this->createPartialMock(\Magento\Eav\Model\Config::class, ['getEntityType']);

        $imageBackendModel = $this->objectManager->getObject(
            \Magento\Catalog\Model\Category\Attribute\Backend\Image::class
        );

        $collection = new \Magento\Framework\DataObject(['attribute_collection' => [
            new \Magento\Framework\DataObject([
                'attribute_code' => 'image',
                'backend' => $imageBackendModel
            ]),
            new \Magento\Framework\DataObject([
                'attribute_code' => 'name',
                'backend' => new \Magento\Framework\DataObject()
            ]),
            new \Magento\Framework\DataObject([
                'attribute_code' => 'level',
                'backend' => new \Magento\Framework\DataObject()
            ]),
        ]]);

        $eavConfig->expects($this->once())
            ->method('getEntityType')
            ->with(\Magento\Catalog\Api\Data\CategoryAttributeInterface::ENTITY_TYPE_CODE)
            ->willReturn($collection);

        $model = $this->objectManager->getObject(\Magento\Catalog\Controller\Adminhtml\Category\Save::class, [
            'eavConfig' => $eavConfig
        ]);

        $result = $model->imagePreprocessing($data);

        $this->assertEquals($expected, $result);
    }
}
