<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Controller\Adminhtml\Report\Product;

use Magento\Backend\Helper\Data;
use Magento\Backend\Model\Session;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Page\Title;
use Magento\Reports\Controller\Adminhtml\Report\Product\Viewed;
use Magento\Reports\Model\Flag;
use Magento\Reports\Test\Unit\Controller\Adminhtml\Report\AbstractControllerTest;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ViewedTest extends AbstractControllerTest
{
    /**
     * @var \Magento\Reports\Controller\Adminhtml\Report\Product\Viewed
     */
    protected $viewed;

    /**
     * @var Date|MockObject
     */
    protected $dateMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManagerMock;

    /**
     * @var Data|MockObject
     */
    protected $helperMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $messageManagerMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->dateMock = $this->getMockBuilder(Date::class)
            ->disableOriginalConstructor()
            ->getMock();

        $flagMock = $this->getMockBuilder(Flag::class)
            ->disableOriginalConstructor()
            ->getMock();
        $flagMock
            ->expects($this->any())
            ->method('setReportFlagCode')
            ->willReturnSelf();
        $flagMock
            ->expects($this->any())
            ->method('loadSelf')
            ->willReturnSelf();

        $this->helperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->objectManagerMock
            ->expects($this->any())
            ->method('create')
            ->with(Flag::class)
            ->willReturn($flagMock);

        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $flagMock = $this->getMockBuilder(ActionFlag::class)
            ->disableOriginalConstructor()
            ->getMock();

        $responseMock = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setRedirect', 'sendResponse'])
            ->getMockForAbstractClass();

        $this->contextMock->expects($this->any())->method('getObjectManager')->willReturn($this->objectManagerMock);
        $this->contextMock->expects($this->any())->method('getHelper')->willReturn($this->helperMock);
        $this->contextMock->expects($this->any())->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->any())->method('getActionFlag')->willReturn($flagMock);
        $this->contextMock->expects($this->any())->method('getResponse')->willReturn($responseMock);

        $objectManager = new ObjectManager($this);
        $this->viewed = $objectManager->getObject(
            Viewed::class,
            [
                'context' => $this->contextMock,
                'fileFactory' => $this->fileFactoryMock,
                'dateFilter' => $this->dateMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $this->objectManagerMock
            ->expects($this->any())
            ->method('get')
            ->willReturn($this->helperMock);

        $titleMock = $this->getMockBuilder(Title::class)
            ->disableOriginalConstructor()
            ->getMock();

        $titleMock
            ->expects($this->once())
            ->method('prepend')
            ->with(new Phrase('Product Views Report'));

        $this->viewMock
            ->expects($this->once())
            ->method('getPage')
            ->willReturn(
                new DataObject(
                    ['config' => new DataObject(
                        ['title' => $titleMock]
                    )]
                )
            );

        $this->menuBlockMock
            ->expects($this->once())
            ->method('setActive')
            ->with('Magento_Reports::report_products_viewed');

        $this->breadcrumbsBlockMock
            ->expects($this->exactly(3))
            ->method('addLink')
            ->withConsecutive(
                [new Phrase('Reports'), new Phrase('Reports')],
                [new Phrase('Products'), new Phrase('Products')],
                [new Phrase('Products Most Viewed Report'), new Phrase('Products Most Viewed Report')]
            );

        $this->viewMock
            ->expects($this->once())
            ->method('renderLayout');

        $this->viewed->execute();
    }

    /**
     * @return void
     */
    public function testExecuteWithException()
    {
        $errorText = new Phrase(
            'An error occurred while showing the product views report. ' .
            'Please review the log and try again.'
        );

        $logMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $sessionMock = $this->getMockBuilder(Session::class)
            ->setMethods(['setIsUrlNotice'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerMock
            ->expects($this->any())
            ->method('get')
            ->willReturnMap(
                [
                    [LoggerInterface::class, $logMock],
                    [\Magento\Backend\Model\Auth\Session::class, $sessionMock]
                ]
            );

        $this->messageManagerMock
            ->expects($this->once())
            ->method('addError')
            ->with($errorText);

        $logMock
            ->expects($this->once())
            ->method('critical');
        $sessionMock
            ->expects($this->once())
            ->method('setIsUrlNotice');

        $this->menuBlockMock
            ->expects($this->once())
            ->method('setActive')
            ->willThrowException(new \Exception());

        $this->viewed->execute();
    }

    /**
     * @return void
     */
    public function testExecuteWithLocalizedException()
    {
        $errorText = new Phrase('Error');

        $this->messageManagerMock
            ->expects($this->once())
            ->method('addError')
            ->with($errorText);

        $this->menuBlockMock
            ->expects($this->once())
            ->method('setActive')
            ->willThrowException(new LocalizedException($errorText));

        $this->viewed->execute();
    }
}
