<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Category;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeleteTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Catalog\Controller\Adminhtml\Category\Delete */
    protected $unit;

    /** @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject */
    protected $resultRedirect;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $request;

    /** @var \Magento\Catalog\Api\CategoryRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $categoryRepository;

    /** @var \Magento\Backend\Model\Auth\StorageInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $authStorage;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $context = $this->getMock(\Magento\Backend\App\Action\Context::class, [], [], '', false);
        $resultRedirectFactory = $this->getMock(
            \Magento\Backend\Model\View\Result\RedirectFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParam', 'isPost'])
            ->getMock();
        $this->request->expects($this->any())->method('isPost')->willReturn(true);
        $auth = $this->getMock(
            \Magento\Backend\Model\Auth::class,
            ['getAuthStorage'],
            [],
            '',
            false
        );
        $this->authStorage = $this->getMock(
            \Magento\Backend\Model\Auth\StorageInterface::class,
            ['processLogin', 'processLogout', 'isLoggedIn', 'prolong', 'setDeletedPath'],
            [],
            '',
            false
        );
        $eventManager = $this->getMockForAbstractClass(
            \Magento\Framework\Event\ManagerInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['dispatch']
        );
        $response = $this->getMockForAbstractClass(
            \Magento\Framework\App\ResponseInterface::class,
            [],
            '',
            false
        );
        $messageManager = $this->getMockForAbstractClass(
            \Magento\Framework\Message\ManagerInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['addSuccessMessage']
        );
        $this->categoryRepository = $this->getMock(\Magento\Catalog\Api\CategoryRepositoryInterface::class);
        $context->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);
        $context->expects($this->any())
            ->method('getResponse')
            ->willReturn($response);
        $context->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($messageManager);
        $context->expects($this->any())
            ->method('getEventManager')
            ->willReturn($eventManager);
        $context->expects($this->any())
            ->method('getAuth')
            ->willReturn($auth);
        $context->expects($this->once())->method('getResultRedirectFactory')->willReturn($resultRedirectFactory);
        $auth->expects($this->any())
            ->method('getAuthStorage')
            ->willReturn($this->authStorage);

        $this->resultRedirect = $this->getMock(\Magento\Backend\Model\View\Result\Redirect::class, [], [], '', false);
        $resultRedirectFactory->expects($this->any())->method('create')->willReturn($this->resultRedirect);

        $this->unit = (new ObjectManagerHelper($this))->getObject(
            \Magento\Catalog\Controller\Adminhtml\Category\Delete::class,
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
        $category = $this->getMock(\Magento\Catalog\Model\Category::class, ['getParentId', 'getPath'], [], '', false);
        $category->expects($this->once())->method('getParentId')->willReturn($parentId);
        $category->expects($this->once())->method('getPath')->willReturn('category-path');
        $this->categoryRepository->expects($this->once())->method('get')->with($categoryId)->willReturn($category);
        $this->authStorage->expects($this->once())->method('setDeletedPath')->with('category-path');
        $this->resultRedirect->expects($this->once())->method('setPath')
            ->with('catalog/*/', ['_current' => true, 'id' => $parentId]);

        $this->unit->execute();
    }
}
