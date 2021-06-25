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
    private $transparentMock;

    /**
     * @var RequestSecureToken
     */
    private $controller;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var JsonFactory|MockObject
     */
    private $resultJsonFactoryMock;

    /**
     * @var Generic|MockObject
     */
    private $sessionTransparentMock;

    /**
     * @var SecureToken|MockObject
     */
    private $secureTokenServiceMock;

    /**
     * @var SessionManager|MockObject
     */
    private $sessionManagerMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->resultJsonFactoryMock = $this->createMock(JsonFactory::class);
        $this->sessionTransparentMock = $this->getMockBuilder(Generic::class)
            ->addMethods(['setQuoteId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->secureTokenServiceMock = $this->getMockBuilder(SecureToken::class)
            ->setMethods(['requestToken'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionManagerMock = $this->getMockBuilder(SessionManager::class)
            ->addMethods(['getQuote'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->transparentMock = $this->getMockBuilder(Transparent::class)
            ->setMethods(['getCode', 'isActive'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = new RequestSecureToken(
            $this->contextMock,
            $this->resultJsonFactoryMock,
            $this->sessionTransparentMock,
            $this->secureTokenServiceMock,
            $this->sessionManagerMock,
            $this->transparentMock
        );
    }

    public function testExecuteSuccess(): void
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

        $quoteMock = $this->createMock(Quote::class);
        $quoteMock->method('getItemsCount')->willReturn(1);
        $quoteMock->method('getStoreId')->willReturn($storeId);

        $tokenMock = $this->createMock(DataObject::class);
        $jsonMock = $this->createMock(Json::class);

        $this->sessionManagerMock->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($quoteMock);
        $this->transparentMock->method('isActive')
            ->with($storeId)
            ->willReturn(true);
        $quoteMock->expects($this->once())
            ->method('getId')
            ->willReturn($quoteId);
        $this->sessionTransparentMock->expects($this->once())
            ->method('setQuoteId')
            ->with($quoteId);
        $this->secureTokenServiceMock->expects($this->once())
            ->method('requestToken')
            ->with($quoteMock)
            ->willReturn($tokenMock);
        $this->transparentMock->method('getCode')
            ->willReturn('transparent');
        $tokenMock->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturnMap(
                [
                    ['', null, $tokenFields],
                    ['securetoken', null, $secureToken]
                ]
            );
        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($jsonMock);
        $jsonMock->expects($this->once())
            ->method('setData')
            ->with($resultExpectation)
            ->willReturnSelf();

        $this->assertEquals($jsonMock, $this->controller->execute());
    }

    public function testExecuteTokenRequestException(): void
    {
        $quoteId = 99;
        $storeId = 2;
        $resultExpectation = [
            'success' => false,
            'error' => true,
            'error_messages' => __('Your payment has been declined. Please try again.')
        ];

        $quoteMock = $this->createMock(Quote::class);
        $quoteMock->method('getItemsCount')->willReturn(1);
        $quoteMock->method('getStoreId')
            ->willReturn($storeId);

        $jsonMock = $this->createMock(Json::class);

        $this->sessionManagerMock->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($quoteMock);
        $quoteMock->expects($this->once())
            ->method('getId')
            ->willReturn($quoteId);
        $this->transparentMock->method('isActive')
            ->with($storeId)
            ->willReturn(true);
        $this->sessionTransparentMock->expects($this->once())
            ->method('setQuoteId')
            ->with($quoteId);
        $this->secureTokenServiceMock->expects($this->once())
            ->method('requestToken')
            ->with($quoteMock)
            ->willThrowException(new \Exception());
        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($jsonMock);
        $jsonMock->expects($this->once())
            ->method('setData')
            ->with($resultExpectation)
            ->willReturnSelf();

        $this->assertEquals($jsonMock, $this->controller->execute());
    }

    public function testExecuteEmptyQuoteError(): void
    {
        $resultExpectation = [
            'success' => false,
            'error' => true,
            'error_messages' => __('Your payment has been declined. Please try again.')
        ];

        $quoteMock = null;
        $jsonMock = $this->createMock(Json::class);

        $this->sessionManagerMock->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($quoteMock);
        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($jsonMock);
        $jsonMock->expects($this->once())
            ->method('setData')
            ->with($resultExpectation)
            ->willReturnSelf();

        $this->assertEquals($jsonMock, $this->controller->execute());
    }

    public function testExecuteNoItemsQuoteError(): void
    {
        $resultExpectation = [
            'success' => false,
            'error' => true,
            'error_messages' => __('Your payment has been declined. Please try again.')
        ];

        $quoteMock = $this->createMock(Quote::class);
        $quoteMock->method('getItemsCount')->willReturn(0);

        $jsonMock = $this->createMock(Json::class);

        $this->sessionManagerMock->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($quoteMock);
        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($jsonMock);
        $jsonMock->expects($this->once())
            ->method('setData')
            ->with($resultExpectation)
            ->willReturnSelf();

        $this->assertEquals($jsonMock, $this->controller->execute());
    }
}
