<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Test\Unit\Controller\Adminhtml\Term;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Search\Controller\Adminhtml\Term\Report;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReportTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ResultFactory|MockObject
     */
    private $resultFactoryMock;

    /**
     * @var Page|MockObject
     */
    private $pageMock;

    /**
     * @var Config|MockObject
     */
    private $pageConfigMock;

    /**
     * @var Title|MockObject
     */
    private $titleMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $eventManagerMock;

    /**
     * @var Report
     */
    private $reportController;

    /**
     * @var MockObject|Context
     */
    private $contextMock;

    protected function setUp(): void
    {
        $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder(Config::class)
            ->setMethods(['getTitle'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->titleMock = $this->getMockBuilder(Title::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->reportController =  $this->objectManagerHelper->getObject(
            Report::class,
            [
                '_eventManager' => $this->eventManagerMock,
                'resultFactory' => $this->resultFactoryMock
            ]
        );
    }

    public function testReport()
    {
        $this->eventManagerMock->expects($this->once())->method('dispatch');

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_PAGE)
            ->willReturn($this->pageMock);
        $this->pageMock->expects($this->once())
            ->method('setActiveMenu')
            ->with('Magento_Search::report_search_term')
            ->willReturnSelf();
        $this->pageMock->expects($this->exactly(2))
            ->method('addBreadcrumb')
            ->withConsecutive([__('Reports'), __('Reports')], [__('Search Terms'), __('Search Terms')])
            ->willReturnSelf();
        $this->pageMock->expects($this->once())
            ->method('getConfig')
            ->willReturn($this->pageConfigMock);
        $this->pageConfigMock->expects($this->once())->method('getTitle')->willReturn($this->titleMock);
        $this->titleMock->expects($this->once())
            ->method('prepend')
            ->with(__('Search Terms Report'))
            ->willReturn($this->pageMock);

        $this->assertSame($this->pageMock, $this->reportController->execute());
    }
}
