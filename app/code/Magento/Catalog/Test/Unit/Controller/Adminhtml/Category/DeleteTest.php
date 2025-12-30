<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Category;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth;
use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Model\Auth\StorageInterface;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Controller\Adminhtml\Category\Delete;
use Magento\Catalog\Model\Category;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeleteTest extends TestCase
{
    use MockCreationTrait;
    /** @var Delete */
    protected $unit;

    /** @var Redirect|MockObject */
    protected $resultRedirect;

    /** @var RequestInterface|MockObject */
    protected $request;

    /** @var CategoryRepositoryInterface|MockObject */
    protected $categoryRepository;

    /** @var StorageInterface|MockObject */
    protected $authStorage;

    protected function setUp(): void
    {

        $objectManager = new ObjectManagerHelper($this);

        $objects = [
            [
                StoreManagerInterface::class,
                $this->createMock(StoreManagerInterface::class)
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
        $objectManager->prepareObjectManager($objects);

        $context = $this->createMock(Context::class);
        $resultRedirectFactory = $this->createPartialMock(
            RedirectFactory::class,
            ['create']
        );
        $this->request = $this->createMock(RequestInterface::class);
        $auth = $this->createPartialMock(Auth::class, ['getAuthStorage']);
        $this->authStorage = $this->createPartialMockWithReflection(
            StorageInterface::class,
            ['setDeletedPath', 'processLogin', 'processLogout', 'isLoggedIn', 'prolong']
        );
        $this->authStorage->method('setDeletedPath')->willReturnSelf();
        $this->authStorage->method('processLogin')->willReturnSelf();
        $this->authStorage->method('processLogout')->willReturnSelf();
        $this->authStorage->method('isLoggedIn')->willReturn(true);
        $this->authStorage->method('prolong')->willReturnSelf();
        $eventManager = $this->createMock(ManagerInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $messageManager = $this->createMock(MessageManagerInterface::class);
        $this->categoryRepository = $this->createMock(CategoryRepositoryInterface::class);
        $context->method('getRequest')->willReturn($this->request);
        $context->method('getResponse')->willReturn($response);
        $context->method('getMessageManager')->willReturn($messageManager);
        $context->method('getEventManager')->willReturn($eventManager);
        $context->method('getAuth')->willReturn($auth);
        $context->expects($this->once())->method('getResultRedirectFactory')->willReturn($resultRedirectFactory);
        $auth->method('getAuthStorage')->willReturn($this->authStorage);

        $this->resultRedirect = $this->createMock(Redirect::class);
        $resultRedirectFactory->method('create')->willReturn($this->resultRedirect);

        $this->unit = $objectManager->getObject(
            Delete::class,
            [
                'context' => $context,
                'categoryRepository' => $this->categoryRepository
            ]
        );
    }

    public function testDeleteWithoutCategoryId()
    {
        $this->request->expects($this->any())->method('getParam')->with('id')->willReturn(null);
        $this->resultRedirect->expects($this->once())->method('setPath')
            ->with('catalog/*/', ['_current' => true, 'id' => null]);
        $this->categoryRepository->expects($this->never())->method('get');

        $this->unit->execute();
    }

    public function testDelete()
    {
        $categoryId = 5;
        $parentId = 7;
        $this->request->expects($this->any())->method('getParam')->with('id')->willReturn($categoryId);
        $category = $this->createPartialMock(Category::class, ['getParentId', 'getPath']);
        $category->expects($this->once())->method('getParentId')->willReturn($parentId);
        $category->expects($this->once())->method('getPath')->willReturn('category-path');
        $this->categoryRepository->expects($this->once())->method('get')->with($categoryId)->willReturn($category);
        $this->authStorage->expects($this->once())->method('setDeletedPath')->with('category-path');
        $this->resultRedirect->expects($this->once())->method('setPath')
            ->with('catalog/*/', ['_current' => true, 'id' => $parentId]);

        $this->unit->execute();
    }
}
