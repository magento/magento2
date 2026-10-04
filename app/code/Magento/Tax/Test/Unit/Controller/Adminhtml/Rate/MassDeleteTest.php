<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Controller\Adminhtml\Rate;

use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Backend\App\Action\Context;
use PHPUnit\Framework\TestCase;
use Magento\Tax\Controller\Adminhtml\Rate\MassDelete;
use Magento\Tax\Api\TaxRateRepositoryInterface;
use Psr\Log\LoggerInterface;

class MassDeleteTest extends TestCase
{
    /**
     * @var TaxRateRepositoryInterface|MockObject
     */
    private $taxRateRepositoryMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var MassDelete
     */
    protected $massDelete;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $messageManagerMock;

    /**
     * @var Redirect|MockObject
     */
    protected $resultRedirectMock;

    /**
     * @var ResultFactory|MockObject
     */
    protected $resultFactory;

    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->contextMock = $this->createMock(Context::class);

        $this->messageManagerMock = $this->createMock(ManagerInterface::class);

        $this->resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->onlyMethods(['setPath'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactory = $this->createMock(ResultFactory::class);
        $this->resultFactory->method('create')->willReturn($this->resultRedirectMock);
        $this->contextMock->method('getResultFactory')->willReturn($this->resultFactory);
        $this->contextMock->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->contextMock->method('getRequest')->willReturn($this->requestMock);

        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->taxRateRepositoryMock = $this->createMock(TaxRateRepositoryInterface::class);
        $this->massDelete = new MassDelete($this->contextMock, $this->taxRateRepositoryMock, $this->loggerMock);
    }

    public function testExecute(): void
    {
        $data = [1];
        $this->requestMock->expects(self::any())
            ->method('getParam')
            ->willReturn($data);

        $this->taxRateRepositoryMock->expects($this->once())
            ->method('deleteById')
            ->with(1)
            ->willReturn(true);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('A total of %1 record(s) have been deleted.', $data));

        $this->massDelete->execute();
    }
}
