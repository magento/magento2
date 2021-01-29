<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Unit\Controller\Adminhtml\Session;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Phrase;

/**
 * Test class for \Magento\Security\Controller\Adminhtml\Session\Activity testing
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ActivityTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var  \Magento\Security\Controller\Adminhtml\Session\Activity
     */
    protected $controller;

    /**
     * @var \Magento\Backend\App\Action\Context
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\App\ViewInterface
     */
    protected $viewMock;

    /**
     * @var  \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * Init mocks for tests
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->contextMock = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->viewMock = $this->createMock(\Magento\Framework\App\ViewInterface::class);
        $this->contextMock->expects($this->any())
            ->method('getView')
            ->willReturn($this->viewMock);

        $this->controller = $this->objectManager->getObject(
            \Magento\Security\Controller\Adminhtml\Session\Activity::class,
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
        $titleMock = $this->getMockBuilder(\Magento\Framework\View\Page\Title::class)
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
