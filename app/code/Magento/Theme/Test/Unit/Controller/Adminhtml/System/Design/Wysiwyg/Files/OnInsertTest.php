<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\Wysiwyg\Files;

class OnInsertTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files */
    protected $controller;

    /** @var \Magento\Framework\App\ViewInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $view;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\PHPUnit_Framework_MockObject_MockObject */
    protected $objectManager;

    /** @var \Magento\Theme\Helper\Storage|\PHPUnit_Framework_MockObject_MockObject */
    protected $storageHelper;

    /** @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject */
    protected $response;

    protected function setUp()
    {
        $this->objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->view = $this->createMock(\Magento\Framework\App\ViewInterface::class);
        $this->storageHelper = $this->createMock(\Magento\Theme\Helper\Storage::class);
        $this->response = $this->createPartialMock(\Magento\Framework\App\Response\Http::class, ['setBody']);

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $helper->getObject(
            \Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files\OnInsert::class,
            [
                'objectManager' => $this->objectManager,
                'view' => $this->view,
                'response' => $this->response
            ]
        );
    }

    public function testExecute()
    {
        $this->objectManager->expects($this->once())
            ->method('get')
            ->with(\Magento\Theme\Helper\Storage::class)
            ->willReturn($this->storageHelper);
        $this->storageHelper
            ->expects($this->once())
            ->method('getRelativeUrl')
            ->willReturn('http://relative.url/');
        $this->response->expects($this->once())
            ->method('setBody')
            ->with('http://relative.url/');

        $this->controller->execute();
    }
}
