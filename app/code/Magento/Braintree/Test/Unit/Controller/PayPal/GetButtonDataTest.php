<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Controller\PayPal;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Braintree\Controller\PayPal\GetButtonData;

/**
 * Class GetButtonDataTest
 *
 * @see \Magento\Braintree\Controller\PayPal\GetButtonData
 */
class GetButtonDataTest extends \PHPUnit_Framework_TestCase
{
    const AMOUNT = 10;

    const CURRENCY = 'USD';

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $checkoutSessionMock;

    /**
     * Run test for execute method
     *
     * @param array $response
     * @param array $data
     * @return void
     *
     * @dataProvider dataProviderTestExecute
     */
    public function testExecute(array $response, array $data)
    {
        $this->contextMock = $this->getMockBuilder('Magento\Framework\App\Action\Context')
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects(static::once())
            ->method('getRequest')
            ->willReturn($this->getRequestMock(true));
        $this->contextMock->expects(static::once())
            ->method('getResultFactory')
            ->willReturn($this->getResultFactoryMock($response));

        $this->checkoutSessionMock = $this->getMockBuilder('Magento\Checkout\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock = $this->getMockBuilder('Magento\Quote\Model\Quote')
            ->setMethods(['getBaseGrandTotal', 'getAllItems', 'getCurrency'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->checkoutSessionMock->expects(static::exactly(3))
            ->method('getQuote')
            ->willReturn($quoteMock);

        $quoteMock->expects(static::once())
            ->method('getAllItems')
            ->willReturn($data['items']);
        $quoteMock->expects(static::once())
            ->method('getBaseGrandTotal')
            ->willReturn($data['amount']);

        $currencyMock = $this->getMockBuilder('Magento\Quote\Api\Data\CurrencyInterface')
            ->getMockForAbstractClass();

        $quoteMock->expects(static::once())
            ->method('getCurrency')
            ->willReturn($currencyMock);

        $currencyMock->expects(static::once())
            ->method('getBaseCurrencyCode')
            ->willReturn($data['currency']);

        $getButtonData = new GetButtonData($this->contextMock, $this->checkoutSessionMock);
        $getButtonData->execute();
    }

    /**
     * @return array
     */
    public function dataProviderTestExecute()
    {
        return [
            [
                'response' => [
                    'isEmpty' => false,
                    'amount' => self::AMOUNT,
                    'currency' => self::CURRENCY,
                ],
                'data' => [
                    'items' => [1,2,3],
                    'amount' => self::AMOUNT,
                    'currency' => self::CURRENCY,
                ]
            ],
            [
                'response' => [
                    'isEmpty' => true,
                    'amount' => 0,
                    'currency' => self::CURRENCY,
                ],
                'data' => [
                    'items' => [],
                    'amount' => 0,
                    'currency' => self::CURRENCY,
                ]
            ]
        ];
    }

    /**
     * @param array $response
     * @return ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getResultFactoryMock(array $response)
    {
        $resultFactoryMock = $this->getMockBuilder('Magento\Framework\Controller\ResultFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $resultJsonMock = $this->getMockBuilder('Magento\Framework\Controller\Result\Json')
            ->disableOriginalConstructor()
            ->getMock();

        $resultFactoryMock->expects(static::once())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($resultJsonMock);

        $resultJsonMock->expects(static::once())
            ->method('setData')
            ->with($response);

        return $resultFactoryMock;
    }

    /**
     * Run test for execute method (exception)
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Wrong type of request.
     */
    public function testExecuteException()
    {
        $this->contextMock = $this->getMockBuilder('Magento\Framework\App\Action\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects(static::once())
            ->method('getRequest')
            ->willReturn($this->getRequestMock(false));

        $this->checkoutSessionMock = $this->getMockBuilder('Magento\Checkout\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();
        $this->checkoutSessionMock->expects(static::never())
        ->method('getQuote');

        $getButtonData = new GetButtonData($this->contextMock, $this->checkoutSessionMock);
        $getButtonData->execute();
    }

    /**
     * @param bool $result
     * @return RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getRequestMock($result)
    {
        $requestMock = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->getMockForAbstractClass();

        $requestMock->expects(static::once())
            ->method('getParam')
            ->with('isAjax')
            ->willReturn($result);

        return $requestMock;
    }
}
