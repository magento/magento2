<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\Wysiwyg\Files;

use Magento\Framework\App\Response\Http;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files;
use Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files\OnInsert;
use Magento\Theme\Helper\Storage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OnInsertTest extends TestCase
{
    /** @var Files */
    protected $controller;

    /** @var ViewInterface|MockObject */
    protected $view;

    /** @var MockObject|MockObject */
    protected $objectManager;

    /** @var Storage|MockObject */
    protected $storageHelper;

    /** @var Http|MockObject */
    protected $response;

    protected function setUp(): void
    {
        $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->view = $this->getMockForAbstractClass(ViewInterface::class);
        $this->storageHelper = $this->createMock(Storage::class);
        $this->response = $this->createPartialMock(Http::class, ['setBody']);

        $helper = new ObjectManager($this);
        $this->controller = $helper->getObject(
            OnInsert::class,
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
            ->with(Storage::class)
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
