<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model\Payflow\Service\Response\Handler;

use Magento\Framework\Object;
use Magento\Payment\Model\InfoInterface;
use Magento\Paypal\Model\Payflow\Service\Response\Handler\FraudHandler;
use Magento\Paypal\Model\Info;
use Magento\Paypal\Model\Payflowpro;

class FraudHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InfoInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentMock;

    /**
     * @var Object | \PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var FraudHandler
     */
    private $fraudHandler;

    /**
     * @var Info | \PHPUnit_Framework_MockObject_MockObject
     */
    private $paypalInfoManagerMock;

    public function setUp()
    {
        $this->paymentMock = $this->getMockBuilder('Magento\Payment\Model\InfoInterface')
            ->getMock();
        $this->responseMock = $this->getMockBuilder('Magento\Framework\Object')
            ->disableOriginalConstructor()
            ->getMock();
        $this->paypalInfoManagerMock = $this->getMockBuilder('Magento\Paypal\Model\Info')
            ->disableOriginalConstructor()
            ->getMock();

        $this->fraudHandler = new FraudHandler($this->paypalInfoManagerMock);
    }

    public function testHandleApprovedTransaction()
    {
        $this->responseMock->expects($this->once())
            ->method('getData')
            ->with('result')
            ->willReturn(Payflowpro::STATUS_APPROVED);

        $this->paypalInfoManagerMock->expects($this->never())
            ->method('importToPayment');

        $this->fraudHandler->handle($this->paymentMock, $this->responseMock);
    }

    /**
     * @dataProvider handleMessagesDataProvider
     */
    public function testHandle($message, $rulesString, $existingFrauds, $expectedMessage)
    {
        $this->responseMock->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturnMap(
                [
                    [FraudHandler::RESPONSE_MESSAGE, null, $message],
                    [FraudHandler::FRAUD_RULES_XML, null, $rulesString],
                    ['result', null, Payflowpro::RESPONSE_CODE_FRAUDSERVICE_FILTER]
                ]
            );

        $this->paymentMock->expects($this->once())
            ->method('getAdditionalInformation')
            ->with(Info::FRAUD_FILTERS)
            ->willReturn($existingFrauds);
        $this->paypalInfoManagerMock->expects($this->once())
            ->method('importToPayment')
            ->with(
                [
                    Info::FRAUD_FILTERS => $expectedMessage
                ]
            );

        $this->fraudHandler->handle($this->paymentMock, $this->responseMock);
    }

    /**
     * @return array
     */
    public function handleMessagesDataProvider()
    {
        return [
            ['Fraud message', null, null, ['RESPMSG' => 'Fraud message']],
            [
                'New fraud message',
                '<?xml version="1.0"?>',
                ['RESPMSG' => 'Existing fraud message'],
                ['RESPMSG' => 'Existing fraud message']
            ],
            [
                'New fraud message',
                $this->getRulesXmlString(),
                [
                    'Total Purchase Price Ceiling' => 'Existing fraud message',
                    'RESPMSG' => 'Existing fraud message'
                ],
                array_merge(
                    $this->getRulesExpectedDictionary(),
                    [
                        'Total Purchase Price Ceiling' => 'Existing fraud message',
                        'RESPMSG' => 'Existing fraud message'
                    ]
                )
            ]
        ];
    }

    /**
     * Returns rules xml list as string
     *
     * @return string
     */
    private function getRulesXmlString()
    {
        return file_get_contents(__DIR__ .'/_files/fps_prexmldata.xml');
    }

    /**
     * Returns expected rules dictionary
     *
     * @return array
     */
    private function getRulesExpectedDictionary()
    {
        return [
            'Total Purchase Price Ceiling' =>
                'The purchase amount of 7501 is greater than the ceiling value set of 7500',
            'Total ItemCeiling' =>
                '16 items were ordered, which is overthe maximum allowed quantity of 15',
            'Shipping/BillingMismatch' =>
                'Thebilling and shipping addresses did not match',
            'BIN Risk List Match' =>
                'The card number is in a high risk bin list',
            'Zip Risk List Match' =>
                'High risk shipping zip',
            'USPS Address Validation Failure' =>
                'The billing address is not a valid USAddress'
        ];
    }
}
