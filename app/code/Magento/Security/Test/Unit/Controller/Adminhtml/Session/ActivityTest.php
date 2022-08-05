<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Test\Unit\Controller\Adminhtml\Session;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Page\Title;
use Magento\Security\Controller\Adminhtml\Session\Activity;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Security\Controller\Adminhtml\Session\Activity testing
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ActivityTest extends TestCase
{
    /**
     * @var  \Magento\Security\Controller\Adminhtml\Session\Activity
     */
    protected $controller;

    /**
     * @var Context
     */
    protected $contextMock;

    /**
     * @var ViewInterface
     */
    protected $viewMock;

    /**
     * @var  ObjectManager
     */
    protected $objectManager;

    /**
     * Init mocks for tests
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->viewMock = $this->getMockForAbstractClass(ViewInterface::class);
        $this->contextMock->expects($this->any())
            ->method('getView')
            ->willReturn($this->viewMock);

        $this->controller = $this->objectManager->getObject(
            Activity::class,
            [
                'context' => $this->contextMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $titleMock = $this->getMockBuilder(Title::class)
            ->disableOriginalConstructor()
            ->getMock();
        $titleMock
            ->expects($this->once())
            ->method('prepend')
            ->with(new Phrase('Account Activity'));
        $this->viewMock
            ->expects($this->any())
            ->method('getPage')
            ->willReturn(
                new DataObject(
                    ['config' => new DataObject(
                        ['title' => $titleMock]
                    )]
                )
            );
        $this->controller->execute();
    }
}
