<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Controller\Transparent;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Session\Generic;
use Magento\Framework\Session\SessionManager;
use Magento\Paypal\Controller\Transparent\RequestSecureToken;
use Magento\Paypal\Model\Payflow\Service\Request\SecureToken;
use Magento\Paypal\Model\Payflow\Transparent;
use Magento\Quote\Model\Quote;

/**
 * Class RequestSecureTokenTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RequestSecureTokenTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Transparent|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transparentMock;

    /**
     * @var RequestSecureToken|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $controller;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultJsonFactoryMock;

    /**
     * @var Generic|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionTransparentMock;

    /**
     * @var SecureToken|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $secureTokenServiceMock;

    /**
     * @var SessionManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionManagerMock;

    /**
     * @var Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $formKeyValidator;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {

        $request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods(['isPost'])
            ->getMockForAbstractClass();
        $request->expects($this->any())->method('isPost')->willReturn(true);
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->method('getRequest')
            ->willReturn($request);

        $this->resultJsonFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\JsonFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionTransparentMock = $this->getMockBuilder(\Magento\Framework\Session\Generic::class)
            ->setMethods(['setQuoteId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->secureTokenServiceMock = $this->getMockBuilder(
            \Magento\Paypal\Model\Payflow\Service\Request\SecureToken::class
        )
            ->setMethods(['requestToken'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionManagerMock = $this->getMockBuilder(\Magento\Framework\Session\SessionManager::class)
            ->setMethods(['getQuote'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->transparentMock = $this->getMockBuilder(\Magento\Paypal\Model\Payflow\Transparent::class)
            ->setMethods(['getCode'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->formKeyValidator = $this->getMockBuilder(Validator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = new \Magento\Paypal\Controller\Transparent\RequestSecureToken(
            $this->contextMock,
            $this->resultJsonFactoryMock,
            $this->sessionTransparentMock,
            $this->secureTokenServiceMock,
            $this->sessionManagerMock,
            $this->transparentMock,
            null,
            $this->formKeyValidator
        );
    }

    public function testExecuteSuccess()
    {
        $quoteId = 99;
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
        $tokenMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->formKeyValidator->method('validate')
            ->willReturn(true);
        $this->sessionManagerMock->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($quoteMock);
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
        $this->transparentMock->expects($this->once())
            ->method('getCode')
            ->willReturn('transparent');
        $tokenMock->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturnMap(
                [
                    ['', null, $tokenFields],
                    ['securetoken', null, $secureToken]
                ]
            );
        $jsonResult = $this->getJsonResult($resultExpectation);

        $this->assertEquals($jsonResult, $this->controller->execute());
    }

    public function testExecuteTokenRequestException()
    {
        $quoteId = 99;
        $resultExpectation = [
            'success' => false,
            'error' => true,
            'error_messages' => __('Your payment has been declined. Please try again.')
        ];

        $quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->formKeyValidator->method('validate')
            ->willReturn(true);
        $this->sessionManagerMock->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($quoteMock);
        $quoteMock->expects($this->once())
            ->method('getId')
            ->willReturn($quoteId);
        $this->sessionTransparentMock->expects($this->once())
            ->method('setQuoteId')
            ->with($quoteId);
        $this->secureTokenServiceMock->expects($this->once())
            ->method('requestToken')
            ->with($quoteMock)
            ->willThrowException(new \Exception());

        $jsonResult = $this->getJsonResult($resultExpectation);

        $this->assertEquals($jsonResult, $this->controller->execute());
    }

    /**
     * Tests error generation.
     *
     * @param Quote|null $quote
     * @param bool $isValidToken
     * @return void
     * @dataProvider executeErrorDataProvider
     */
    public function testExecuteError($quote, bool $isValidToken)
    {
        $resultExpectation = [
            'success' => false,
            'error' => true,
            'error_messages' => __('Your payment has been declined. Please try again.')
        ];

        $this->sessionManagerMock->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($quote);
        $this->formKeyValidator->method('validate')
            ->willReturn($isValidToken);

        $jsonResult = $this->getJsonResult($resultExpectation);

        $this->assertEquals($jsonResult, $this->controller->execute());
    }

    /**
     * @return array
     */
    public function executeErrorDataProvider()
    {
        $quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();

        return [
            'empty quote' => [null, true],
            'invalid CSRF token' => [$quote, false]
        ];
    }

    /**
     * Returns json result.
     *
     * @param array $result
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getJsonResult(array $result): \PHPUnit_Framework_MockObject_MockObject
    {
        $jsonMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $jsonMock->expects($this->once())
            ->method('setData')
            ->with($result)
            ->willReturnSelf();
        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($jsonMock);

        return $jsonMock;
    }
}
