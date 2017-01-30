<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Category;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

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

    protected function setUp()
    {
        $context = $this->getMock('Magento\Backend\App\Action\Context', [], [], '', false);
        $resultRedirectFactory = $this->getMock(
            'Magento\Backend\Model\View\Result\RedirectFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->request = $this->getMockForAbstractClass(
            'Magento\Framework\App\RequestInterface',
            [],
            '',
            false,
            true,
            true,
            ['getParam', 'getPost']
        );
        $auth = $this->getMock(
            'Magento\Backend\Model\Auth',
            ['getAuthStorage'],
            [],
            '',
            false
        );
        $this->authStorage = $this->getMock(
            'Magento\Backend\Model\Auth\StorageInterface',
            ['processLogin', 'processLogout', 'isLoggedIn', 'prolong', 'setDeletedPath'],
            [],
            '',
            false
        );
        $eventManager = $this->getMockForAbstractClass(
            'Magento\Framework\Event\ManagerInterface',
            [],
            '',
            false,
            true,
            true,
            ['dispatch']
        );
        $response = $this->getMockForAbstractClass(
            'Magento\Framework\App\ResponseInterface',
            [],
            '',
            false
        );
        $messageManager = $this->getMockForAbstractClass(
            'Magento\Framework\Message\ManagerInterface',
            [],
            '',
            false,
            true,
            true,
            ['addSuccess']
        );
        $this->categoryRepository = $this->getMock('Magento\Catalog\Api\CategoryRepositoryInterface');
        $context->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->request));
        $context->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($response));
        $context->expects($this->any())
            ->method('getMessageManager')
            ->will($this->returnValue($messageManager));
        $context->expects($this->any())
            ->method('getEventManager')
            ->will($this->returnValue($eventManager));
        $context->expects($this->any())
            ->method('getAuth')
            ->will($this->returnValue($auth));
        $context->expects($this->once())->method('getResultRedirectFactory')->willReturn($resultRedirectFactory);
        $auth->expects($this->any())
            ->method('getAuthStorage')
            ->will($this->returnValue($this->authStorage));

        $this->resultRedirect = $this->getMock('Magento\Backend\Model\View\Result\Redirect', [], [], '', false);
        $resultRedirectFactory->expects($this->any())->method('create')->willReturn($this->resultRedirect);

        $this->unit = (new ObjectManagerHelper($this))->getObject(
            'Magento\Catalog\Controller\Adminhtml\Category\Delete',
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
        $category = $this->getMock('Magento\Catalog\Model\Category', ['getParentId', 'getPath'], [], '', false);
        $category->expects($this->once())->method('getParentId')->willReturn($parentId);
        $category->expects($this->once())->method('getPath')->willReturn('category-path');
        $this->categoryRepository->expects($this->once())->method('get')->with($categoryId)->willReturn($category);
        $this->authStorage->expects($this->once())->method('setDeletedPath')->with('category-path');
        $this->resultRedirect->expects($this->once())->method('setPath')
            ->with('catalog/*/', ['_current' => true, 'id' => $parentId]);

        $this->unit->execute();
    }
}
