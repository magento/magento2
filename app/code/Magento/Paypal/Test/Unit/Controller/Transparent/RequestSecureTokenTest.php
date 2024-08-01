<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Controller\Transparent;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Session\Generic;
use Magento\Framework\Session\SessionManager;
use Magento\Paypal\Controller\Transparent\RequestSecureToken;
use Magento\Paypal\Model\Payflow\Service\Request\SecureToken;
use Magento\Paypal\Model\Payflow\Transparent;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RequestSecureTokenTest extends TestCase
{
    /**
     * @var Transparent|MockObject
     */
    private $transparent;

    /**
     * @var RequestSecureToken|MockObject
     */
    private $controller;

    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var JsonFactory|MockObject
     */
    private $resultJsonFactory;

    /**
     * @var Generic|MockObject
     */
    private $sessionTransparent;

    /**
     * @var SecureToken|MockObject
     */
    private $secureTokenService;

    /**
     * @var SessionManager|MockObject
     */
    private $sessionManager;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultJsonFactory = $this->getMockBuilder(JsonFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionTransparent = $this->getMockBuilder(Generic::class)
            ->addMethods(['setQuoteId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->secureTokenService = $this->getMockBuilder(
            SecureToken::class
        )
            ->onlyMethods(['requestToken'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionManager = $this->getMockBuilder(SessionManager::class)
            ->addMethods(['getQuote'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->transparent = $this->getMockBuilder(Transparent::class)
            ->onlyMethods(['getCode', 'isActive'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = new RequestSecureToken(
            $this->context,
            $this->resultJsonFactory,
            $this->sessionTransparent,
            $this->secureTokenService,
            $this->sessionManager,
            $this->transparent
        );
    }

    public function testExecuteSuccess()
    {
        $quoteId = 99;
        $storeId = 2;
        $tokenFields = ['fields-1', 'fields-2', 'fields-3'];
        $secureToken = 'token_hash';
        $resultExpectation = [
            'transparent' => [
                'fields' => ['fields-1', 'fields-2', 'fields-3']
            ],
            'success' => true,
            'error' => false
        ];

        $quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock->method('getStoreId')
            ->willReturn($storeId);
        $tokenMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $jsonMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sessionManager->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($quoteMock);
        $this->transparent->method('isActive')
            ->with($storeId)
            ->willReturn(true);
        $quoteMock->expects($this->once())
            ->method('getId')
            ->willReturn($quoteId);
        $this->sessionTransparent->expects($this->once())
            ->method('setQuoteId')
            ->with($quoteId);
        $this->secureTokenService->expects($this->once())
            ->method('requestToken')
            ->with($quoteMock)
            ->willReturn($tokenMock);
        $this->transparent->method('getCode')
            ->willReturn('transparent');
        $tokenMock->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturnMap(
                [
                    ['', null, $tokenFields],
                    ['securetoken', null, $secureToken]
                ]
            );
        $this->resultJsonFactory->expects($this->once())
            ->method('create')
            ->willReturn($jsonMock);
        $jsonMock->expects($this->once())
            ->method('setData')
            ->with($resultExpectation)
            ->willReturnSelf();

        $this->assertEquals($jsonMock, $this->controller->execute());
    }

    public function testExecuteTokenRequestException()
    {
        $quoteId = 99;
        $storeId = 2;
        $resultExpectation = [
            'success' => false,
            'error' => true,
            'error_messages' => __('Your payment has been declined. Please try again.')
        ];

        $quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock->method('getStoreId')
            ->willReturn($storeId);
        $jsonMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sessionManager->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($quoteMock);
        $quoteMock->expects($this->once())
            ->method('getId')
            ->willReturn($quoteId);
        $this->transparent->method('isActive')
            ->with($storeId)
            ->willReturn(true);
        $this->sessionTransparent->expects($this->once())
            ->method('setQuoteId')
            ->with($quoteId);
        $this->secureTokenService->expects($this->once())
            ->method('requestToken')
            ->with($quoteMock)
            ->willThrowException(new \Exception());
        $this->resultJsonFactory->expects($this->once())
            ->method('create')
            ->willReturn($jsonMock);
        $jsonMock->expects($this->once())
            ->method('setData')
            ->with($resultExpectation)
            ->willReturnSelf();

        $this->assertEquals($jsonMock, $this->controller->execute());
    }

    public function testExecuteEmptyQuoteError()
    {
        $resultExpectation = [
            'success' => false,
            'error' => true,
            'error_messages' => __('Your payment has been declined. Please try again.')
        ];

        $quoteMock = null;
        $jsonMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sessionManager->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($quoteMock);
        $this->resultJsonFactory->expects($this->once())
            ->method('create')
            ->willReturn($jsonMock);
        $jsonMock->expects($this->once())
            ->method('setData')
            ->with($resultExpectation)
            ->willReturnSelf();

        $this->assertEquals($jsonMock, $this->controller->execute());
    }
}
