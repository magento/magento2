<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Category;

use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Catalog\Api\Data\CategoryAttributeInterface;
use Magento\Catalog\Controller\Adminhtml\Category\Save;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Category\Attribute\Backend\Image;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Message\Collection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Messages;
use Magento\Framework\View\Layout;
use Magento\Framework\View\LayoutFactory;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends TestCase
{
    /**
     * @var RedirectFactory|MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var JsonFactory|MockObject
     */
    private $resultJsonFactoryMock;

    /**
     * @var LayoutFactory|MockObject
     */
    private $layoutFactoryMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $eventManagerMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|MockObject
     */
    private $messageManagerMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Save
     */
    private $save;

    /**
     * Set up.
     *
     * @inheritdoc
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $objects = [
            [
                StoreManagerInterface::class,
                $this->createMock(StoreManagerInterface::class)
            ],
            [
                Registry::class,
                $this->createMock(Registry::class)
            ],
            [
                Config::class,
                $this->createMock(Config::class)
            ],
            [
                Session::class,
                $this->createMock(Session::class)
            ]
        ];
        $this->objectManager->prepareObjectManager($objects);

        $this->resultRedirectFactoryMock = $this->createPartialMock(
            RedirectFactory::class,
            ['create']
        );
        $this->resultJsonFactoryMock = $this->createPartialMock(
            JsonFactory::class,
            ['create']
        );
        $this->layoutFactoryMock = $this->createPartialMock(LayoutFactory::class, ['create']);
        $this->requestMock = $this->getMockForAbstractClass(
            RequestInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getParam', 'getPost', 'getPostValue']
        );
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->eventManagerMock = $this->getMockForAbstractClass(
            ManagerInterface::class,
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
            Save::class,
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
     * Run test execute method.
     *
     * @param int|bool $categoryId
     * @param int $storeId
     * @param int|null $parentId
     *
     * @return void
     * @dataProvider dataProviderExecute
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecute($categoryId, $storeId, $parentId): void
    {
        $this->markTestSkipped('Due to MAGETWO-48956');

        $rootCategoryId = Category::TREE_ROOT_ID;
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
         * @var Redirect|MockObject $resultRedirectMock
         */
        $resultRedirectMock = $this->createMock(Redirect::class);
        /**
         * @var Messages|MockObject $blockMock
         */
        $blockMock = $this->createPartialMock(
            Messages::class,
            ['setMessages', 'getGroupedHtml']
        );
        /**
         * @var \Magento\Catalog\Model\Category|MockObject $categoryMock
         */
        $categoryMock = $this->getMockBuilder(Category::class)
            ->addMethods(['setAttributeSetId', 'getProductsReadonly', 'setPostedProducts'])
            ->onlyMethods(
                [
                    'setStoreId',
                    'load',
                    'getPath',
                    'getResource',
                    'setPath',
                    'setParentId',
                    'setData',
                    'addData',
                    'getDefaultAttributeSetId',
                    'getId',
                    'validate',
                    'unsetData',
                    'save',
                    'toArray'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        /**
         * @var \Magento\Catalog\Model\Category|MockObject $parentCategoryMock
         */
        $parentCategoryMock = $this->getMockBuilder(Category::class)
            ->addMethods(['setAttributeSetId', 'getProductsReadonly', 'setPostedProducts'])
            ->onlyMethods(
                [
                    'setStoreId',
                    'load',
                    'getPath',
                    'setPath',
                    'setParentId',
                    'setData',
                    'addData',
                    'getDefaultAttributeSetId',
                    'getId'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        /**
         * @var Session|MockObject $sessionMock
         */
        $sessionMock = $this->createMock(Session::class);
        /**
         * @var Registry|MockObject $registryMock
         */
        $registryMock = $this->createPartialMock(Registry::class, ['register']);
        /**
         * @var Config|MockObject $wysiwygConfigMock
         */
        $wysiwygConfigMock = $this->getMockBuilder(Config::class)
            ->addMethods(['setStoreId'])
            ->disableOriginalConstructor()
            ->getMock();
        /**
         * @var StoreManagerInterface|MockObject $storeManagerMock
         */
        $storeManagerMock = $this->getMockForAbstractClass(
            StoreManagerInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getStore', 'getRootCategoryId']
        );
        /**
         * @var Layout|MockObject $layoutMock
         */
        $layoutMock = $this->getMockForAbstractClass(
            Layout::class,
            [],
            '',
            false,
            true,
            true,
            ['getMessagesBlock']
        );
        /**
         * @var Json|MockObject $resultJsonMock
         */
        $resultJsonMock = $this->createPartialMock(Config::class, ['setData']);
        /**
         * @var Collection|MockObject $messagesMock
         */
        $messagesMock = $this->createMock(Collection::class);

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
                    [Session::class, $sessionMock],
                    [Registry::class, $registryMock],
                    [Config::class, $wysiwygConfigMock],
                    [StoreManagerInterface::class, $storeManagerMock],
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
        $categoryMock->method('getId')->willReturn(111);
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
     * Data provider for execute.
     *
     * @return array
     */
    public static function dataProviderExecute(): array
    {
        return [
            [
                'categoryId' => false,
                'storeId' => 7,
                'parentId' => 123
            ],
            [
                'categoryId' => false,
                'storeId' => 7,
                'parentId' => null
            ]
        ];
    }

    /**
     * @return array
     */
    public static function imagePreprocessingDataProvider(): array
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
    public function testImagePreprocessing($data, $expected): void
    {
        $eavConfig = $this->createPartialMock(\Magento\Eav\Model\Config::class, ['getEntityType']);

        $imageBackendModel = $this->objectManager->getObject(
            Image::class
        );

        $collection = new DataObject(['attribute_collection' => [
            new DataObject([
                'attribute_code' => 'image',
                'backend' => $imageBackendModel
            ]),
            new DataObject([
                'attribute_code' => 'name',
                'backend' => new DataObject()
            ]),
            new DataObject([
                'attribute_code' => 'level',
                'backend' => new DataObject()
            ]),
        ]]);

        $eavConfig->expects($this->once())
            ->method('getEntityType')
            ->with(CategoryAttributeInterface::ENTITY_TYPE_CODE)
            ->willReturn($collection);

        $model = $this->objectManager->getObject(Save::class, [
            'eavConfig' => $eavConfig
        ]);

        $result = $model->imagePreprocessing($data);

        $this->assertEquals($expected, $result);
    }
}
