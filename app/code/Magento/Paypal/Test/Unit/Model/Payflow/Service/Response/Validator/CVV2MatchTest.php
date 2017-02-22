<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model\Payflow\Service\Response\Validator;

use Magento\Paypal\Model\Payflow\Service\Response\Validator\CVV2Match;

/**
 * Class CVV2MatchTest
 *
 * Test class for \Magento\Paypal\Model\Payflow\Service\Response\Validator\CVV2Match
 */
class CVV2MatchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CVV2Match
     */
    protected $validator;

    /**
     * @var \Magento\Payment\Model\Method\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $quoteRepositoryMock = $this->getMock('\Magento\Quote\Api\CartRepositoryInterface');
        $sessionTransparentMock = $this->getMockBuilder('Magento\Framework\Session\Generic')
            ->setMethods(['getQuoteId'])
            ->disableOriginalConstructor()
            ->getMock();
        $paymentManagementMock = $this->getMockBuilder('Magento\Quote\Api\PaymentMethodManagementInterface')
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        $this->configMock = $this->getMockBuilder('Magento\Payment\Model\Method\ConfigInterface')
            ->getMockForAbstractClass();

        $this->setToExpectedCallsInConstructor(
            $quoteRepositoryMock,
            $sessionTransparentMock,
            $paymentManagementMock
        );

        $this->validator = new CVV2Match(
            $quoteRepositoryMock,
            $sessionTransparentMock,
            $paymentManagementMock
        );
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $quoteRepositoryMock
     * @param \PHPUnit_Framework_MockObject_MockObject $sessionTransparentMock
     * @param \PHPUnit_Framework_MockObject_MockObject $paymentManagementMock
     */
    protected function setToExpectedCallsInConstructor(
        \PHPUnit_Framework_MockObject_MockObject $quoteRepositoryMock,
        \PHPUnit_Framework_MockObject_MockObject $sessionTransparentMock,
        \PHPUnit_Framework_MockObject_MockObject $paymentManagementMock
    ) {
        $quoteId = 77;

        $quoteMock = $this->getMockBuilder('Magento\Quote\Api\Data\CartInterface')
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $paymentMock = $this->getMockBuilder('Magento\Quote\Api\Data\PaymentInterface')
            ->setMethods(['getMethodInstance'])
            ->getMockForAbstractClass();
        $paymentInstanceMock = $this->getMockBuilder('Magento\Payment\Model\Method\TransparentInterface')
            ->getMockForAbstractClass();

        $sessionTransparentMock->expects($this->once())
            ->method('getQuoteId')
            ->willReturn($quoteId);

        $quoteRepositoryMock->expects($this->once())
            ->method('get')
            ->with($quoteId)
            ->willReturn($quoteMock);

        $quoteMock->expects($this->once())
            ->method('getId')
            ->willReturn($quoteId);

        $paymentManagementMock->expects($this->once())
            ->method('get')
            ->with($quoteId)
            ->willReturn($paymentMock);

        $paymentMock->expects($this->once())
            ->method('getMethodInstance')
            ->willReturn($paymentInstanceMock);

        $paymentInstanceMock->expects($this->once())
            ->method('getConfigInterface')
            ->willReturn($this->configMock);
    }

    /**
     * @param bool $expectedResult
     * @param \Magento\Framework\DataObject $response
     * @param string $avsSecurityCodeFlag
     *
     * @dataProvider validationDataProvider
     */
    public function testValidation(
        $expectedResult,
        \Magento\Framework\DataObject $response,
        $avsSecurityCodeFlag
    ) {
        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with(CVV2Match::CONFIG_NAME)
            ->willReturn($avsSecurityCodeFlag);

        $this->assertEquals($expectedResult, $this->validator->validate($response));

        if (!$expectedResult) {
            $this->assertNotEmpty($response->getRespmsg());
        }
    }

    /**
     * @return array
     */
    public function validationDataProvider()
    {
        return [
            [
                'expectedResult' => true,
                'response' => new \Magento\Framework\DataObject(
                    [
                        'cvv2match' => 'Y',
                    ]
                ),
                'configValue' => '0',
            ],
            [
                'expectedResult' => true,
                'response' => new \Magento\Framework\DataObject(
                    [
                        'cvv2match' => 'Y',
                    ]
                ),
                'configValue' => '1',
            ],
            [
                'expectedResult' => true,
                'response' => new \Magento\Framework\DataObject(
                    [
                        'cvv2match' => 'X',
                    ]
                ),
                'configValue' => '1',
            ],
            [
                'expectedResult' => false,
                'response' => new \Magento\Framework\DataObject(
                    [
                        'cvv2match' => 'N',
                    ]
                ),
                'configValue' => '1',
            ],
            [
                'expectedResult' => true,
                'response' => new \Magento\Framework\DataObject(
                    [
                        'cvv2match' => null,
                    ]
                ),
                'configValue' => '1',
            ],
            [
                'expectedResult' => true,
                'response' => new \Magento\Framework\DataObject(),
                'configValue' => '1',
            ],
        ];
    }
}
