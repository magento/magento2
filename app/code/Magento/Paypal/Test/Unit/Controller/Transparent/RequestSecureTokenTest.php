<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Controller\Transparent;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Session\Generic;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Paypal\Controller\Transparent\RequestSecureToken;
use Magento\Paypal\Model\Payflow\Service\Request\SecureToken;
use Magento\Paypal\Model\Payflow\Transparent;

/**
 * Class RequestSecureTokenTest
 */
class RequestSecureTokenTest extends \PHPUnit_Framework_TestCase
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
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {

        $this->contextMock = $this->getMockBuilder('Magento\Framework\App\Action\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultJsonFactoryMock = $this->getMockBuilder('Magento\Framework\Controller\Result\JsonFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionTransparentMock = $this->getMockBuilder('Magento\Framework\Session\Generic')
            ->setMethods(['setQuoteId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->secureTokenServiceMock = $this->getMockBuilder(
            'Magento\Paypal\Model\Payflow\Service\Request\SecureToken'
        )
            ->setMethods(['requestToken'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionManagerMock = $this->getMockBuilder('Magento\Framework\Session\SessionManager')
            ->setMethods(['getQuote'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->transparentMock = $this->getMockBuilder('Magento\Paypal\Model\Payflow\Transparent')
            ->setMethods(['getCode'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = new \Magento\Paypal\Controller\Transparent\RequestSecureToken(
            $this->contextMock,
            $this->resultJsonFactoryMock,
            $this->sessionTransparentMock,
            $this->secureTokenServiceMock,
            $this->sessionManagerMock,
            $this->transparentMock
        );
    }

    /**
     * Run test execute method
     *
     * @param array $result
     * @param array $resultExpectation
     *
     * @dataProvider executeDataProvider
     */
    public function testExecute(array $result, array $resultExpectation)
    {
        $quoteId = 99;

        $quoteMock = $this->getMockBuilder('Magento\Quote\Model\Quote')
            ->disableOriginalConstructor()
            ->getMock();
        $tokenMock = $this->getMockBuilder('Magento\Framework\Object')
            ->setMethods(['getData', 'getSecuretoken'])
            ->disableOriginalConstructor()
            ->getMock();
        $jsonMock = $this->getMockBuilder('Magento\Framework\Controller\Result\Json')
            ->disableOriginalConstructor()
            ->getMock();

        $this->transparentMock->expects($this->once())
            ->method('getCode')
            ->willReturn('transparent');
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
        $tokenMock->expects($this->once())
            ->method('getData')
            ->willReturn($result['transparent']['fields']);
        $tokenMock->expects($this->once())
            ->method('getSecuretoken')
            ->willReturn($result['success']);
        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($jsonMock);
        $jsonMock->expects($this->once())
            ->method('setData')
            ->with($resultExpectation)
            ->willReturnSelf();

        $this->assertEquals($jsonMock, $this->controller->execute());
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            [
                'result' => [
                    'transparent' => [
                        'fields' => ['fields-1', 'fields-2', 'fields-3']
                    ],
                    'success' => 1
                ],
                'result_expectation' => [
                    'transparent' => [
                        'fields' => ['fields-1', 'fields-2', 'fields-3']
                    ],
                    'success' => true
                ]
            ],
            [
                'result' => [
                    'transparent' => [
                        'fields' => ['fields-1', 'fields-2', 'fields-3']
                    ],
                    'success' => null,
                ],
                'result_expectation' => [
                    'transparent' => [
                        'fields' => ['fields-1', 'fields-2', 'fields-3']
                    ],
                    'success' => false,
                    'error' => true,
                    'error_messages' => __('Secure Token Error. Try again.')
                ]
            ]
        ];
    }
}
