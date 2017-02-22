<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model\Payflow\Service\Response\Validator;

use Magento\Paypal\Model\Payflow\Service\Response\Validator\AVSResponse;

/**
 * Class AVSResponseTest
 *
 * Test class for \Magento\Paypal\Model\Payflow\Service\Response\Validator\AVSResponse
 */
class AVSResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Paypal\Model\Payflow\Service\Response\Validator\AVSResponse
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

        $this->validator = new AVSResponse(
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
     * @param array $configMap
     * @param int $exactlyCount
     *
     * @dataProvider validationDataProvider
     */
    public function testValidation(
        $expectedResult,
        \Magento\Framework\DataObject $response,
        array $configMap,
        $exactlyCount
    ) {
        $this->configMock->expects($this->exactly($exactlyCount))
            ->method('getValue')
            ->willReturnMap($configMap);

        $this->assertEquals($expectedResult, $this->validator->validate($response));

        if (!$expectedResult) {
            $this->assertNotEmpty($response->getRespmsg());
        }
    }

    /**
     * @return array
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function validationDataProvider()
    {
        return [
            [
                'expectedResult' => true,
                'response' => new \Magento\Framework\DataObject(
                    [
                        'avsaddr' => 'Y',
                        'avszip' => 'Y',
                        'iavs' => 'Y',
                    ]
                ),
                'configMap' => [
                    ['avs_street', null, '0'],
                    ['avs_zip', null, '0'],
                    ['avs_international', null, '0'],
                ],
                'exactlyCount' => 3,
            ],
            [
                'expectedResult' => true,
                'response' => new \Magento\Framework\DataObject(
                    [
                        'avsaddr' => 'Y',
                        'avszip' => 'Y',
                        'iavs' => 'Y',
                    ]
                ),
                'configMap' => [
                    ['avs_street', null, '1'],
                    ['avs_zip', null, '1'],
                    ['avs_international', null, '1'],
                ],
                'exactlyCount' => 3,
            ],
            [
                'expectedResult' => false,
                'response' => new \Magento\Framework\DataObject(
                    [
                        'avsaddr' => 'Y',
                        'avszip' => 'N',
                        'iavs' => 'Y',
                    ]
                ),
                'configMap' => [
                    ['avs_street', null, '1'],
                    ['avs_zip', null, '1'],
                    ['avs_international', null, '1'],
                ],
                'exactlyCount' => 2,
            ],
            [
                'expectedResult' => true,
                'response' => new \Magento\Framework\DataObject(
                    [
                        'avsaddr' => 'Y',
                        'avszip' => 'N',
                        'iavs' => 'N',
                    ]
                ),
                'configMap' => [
                    ['avs_street', null, '1'],
                    ['avs_zip', null, '0'],
                    ['avs_international', null, '0'],
                ],
                'exactlyCount' => 3,
            ],
            [
                'expectedResult' => true,
                'response' => new \Magento\Framework\DataObject(
                    [
                        'avsaddr' => 'Y',
                        'avszip' => 'N',
                        'iavs' => 'N',
                    ]
                ),
                'configMap' => [
                    ['avs_street', null, '0'],
                    ['avs_zip', null, '0'],
                    ['avs_international', null, '0'],
                ],
                'exactlyCount' => 3,
            ],
            [
                'expectedResult' => true,
                'response' => new \Magento\Framework\DataObject(
                    [
                        'avsaddr' => 'X',
                        'avszip' => 'Y',
                        'iavs' => 'X',
                    ]
                ),
                'configMap' => [
                    ['avs_street', null, '1'],
                    ['avs_zip', null, '1'],
                    ['avs_international', null, '1'],
                ],
                'exactlyCount' => 3,
            ],
            [
                'expectedResult' => true,
                'response' => new \Magento\Framework\DataObject(
                    [
                        'avsaddr' => 'X',
                        'avszip' => 'Y',
                        'iavs' => 'X',
                    ]
                ),
                'configMap' => [
                    ['avs_street', null, '1'],
                    ['avs_zip', null, '0'],
                    ['avs_international', null, '1'],
                ],
                'exactlyCount' => 3,
            ],
        ];
    }
}
