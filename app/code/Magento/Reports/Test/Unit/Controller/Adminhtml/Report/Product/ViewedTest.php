<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Unit\Controller\Adminhtml\Report\Product;

use Magento\Reports\Controller\Adminhtml\Report\Product\Viewed;
use Magento\Framework\DataObject;
use Magento\Framework\Phrase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ViewedTest extends \Magento\Reports\Test\Unit\Controller\Adminhtml\Report\AbstractControllerTest
{
    /**
     * @var \Magento\Reports\Controller\Adminhtml\Report\Product\Viewed
     */
    protected $viewed;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dateMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Backend\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->dateMock = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\Filter\Date::class)
            ->disableOriginalConstructor()
            ->getMock();

        $flagMock = $this->getMockBuilder(\Magento\Reports\Model\Flag::class)
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

        $this->helperMock = $this->getMockBuilder(\Magento\Backend\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock
            ->expects($this->any())
            ->method('create')
            ->with(\Magento\Reports\Model\Flag::class)
            ->willReturn($flagMock);

        $this->messageManagerMock = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $flagMock = $this->getMockBuilder(\Magento\Framework\App\ActionFlag::class)
            ->disableOriginalConstructor()
            ->getMock();

        $responseMock = $this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setRedirect', 'sendResponse'])
            ->getMock();

        $this->contextMock->expects($this->any())->method('getObjectManager')->willReturn($this->objectManagerMock);
        $this->contextMock->expects($this->any())->method('getHelper')->willReturn($this->helperMock);
        $this->contextMock->expects($this->any())->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->any())->method('getActionFlag')->willReturn($flagMock);
        $this->contextMock->expects($this->any())->method('getResponse')->willReturn($responseMock);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->viewed = $objectManager->getObject(
            \Magento\Reports\Controller\Adminhtml\Report\Product\Viewed::class,
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

        $titleMock = $this->getMockBuilder(\Magento\Framework\View\Page\Title::class)
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

        $logMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $sessionMock = $this->getMockBuilder(\Magento\Backend\Model\Session::class)
            ->setMethods(['setIsUrlNotice'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerMock
            ->expects($this->any())
            ->method('get')
            ->will(
                $this->returnValueMap(
                    [
                        [\Psr\Log\LoggerInterface::class, $logMock],
                        [\Magento\Backend\Model\Auth\Session::class, $sessionMock]
                    ]
                )
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
            ->willThrowException(new \Magento\Framework\Exception\LocalizedException($errorText));

        $this->viewed->execute();
    }
}
