<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\Wysiwyg\Files;

use Magento\Framework\App\ViewInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files;
use Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files\Index;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    /** @var Files */
    protected $controller;

    /** @var ViewInterface|MockObject */
    protected $view;

    protected function setUp(): void
    {
        $this->view = $this->getMockForAbstractClass(ViewInterface::class);

        $helper = new ObjectManager($this);
        $this->controller = $helper->getObject(
            Index::class,
            [
                'view' => $this->view,
            ]
        );
    }

    public function testExecute()
    {
        $this->view->expects($this->once())
            ->method('loadLayout')
            ->with('overlay_popup');
        $this->view->expects($this->once())
            ->method('renderLayout');

        $this->controller->execute();
    }
}
