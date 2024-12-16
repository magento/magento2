<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model\Payflow\Service\Response\Handler;

use Magento\Framework\DataObject;
use Magento\Framework\Xml\Security;
use Magento\Payment\Model\InfoInterface;
use Magento\Paypal\Model\Info;
use Magento\Paypal\Model\Payflow\Service\Response\Handler\FraudHandler;
use Magento\Paypal\Model\Payflowpro;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FraudHandlerTest extends TestCase
{
    /**
     * @var InfoInterface|MockObject
     */
    private $paymentMock;

    /**
     * @var Object|MockObject
     */
    private $responseMock;

    /**
     * @var FraudHandler
     */
    private $fraudHandler;

    /**
     * @var Info|MockObject
     */
    private $paypalInfoManagerMock;

    protected function setUp(): void
    {
        $this->paymentMock = $this->getMockBuilder(InfoInterface::class)
            ->getMock();
        $this->responseMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paypalInfoManagerMock = $this->getMockBuilder(Info::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fraudHandler = new FraudHandler(
            $this->paypalInfoManagerMock,
            new Security()
        );
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
    public static function handleMessagesDataProvider()
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
                self::getRulesXmlString(),
                [
                    'Total Purchase Price Ceiling' => 'Existing fraud message',
                    'RESPMSG' => 'Existing fraud message'
                ],
                array_merge(
                    self::getRulesExpectedDictionary(),
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
     * @param string $fileName
     * @return string
     */
    private static function getRulesXmlString($fileName = 'fps_prexmldata.xml')
    {
        return file_get_contents(__DIR__ . '/_files/' . $fileName);
    }

    /**
     * Returns expected rules dictionary
     *
     * @return array
     */
    private static function getRulesExpectedDictionary()
    {
        return [
            'Total Purchase Price Ceiling' => 'The purchase amount of 7501 is greater than the ceiling value set of 750'
                . '0',
            'Total ItemCeiling' => '16 items were ordered, which is overthe maximum allowed quantity of 15',
            'Shipping/BillingMismatch' => 'Thebilling and shipping addresses did not match',
            'BIN Risk List Match' => 'The card number is in a high risk bin list',
            'Zip Risk List Match' => 'High risk shipping zip',
            'USPS Address Validation Failure' => 'The billing address is not a valid USAddress'
        ];
    }

    /**
     * Check attempting to read invalid XML file (XXE XML)
     */
    public function testHandleXXEXml()
    {
        $file = __DIR__ . '/_files/xxe-xml.txt';
        $rulesString = str_replace('{file}', $file, $this->getRulesXmlString('xxe_fps_prexmldata.xml'));

        $this->responseMock->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturnMap(
                [
                    [FraudHandler::RESPONSE_MESSAGE, null, 'New fraud message'],
                    [FraudHandler::FRAUD_RULES_XML, null, $rulesString],
                    ['result', null, Payflowpro::RESPONSE_CODE_FRAUDSERVICE_FILTER]
                ]
            );
        $this->paymentMock->expects($this->once())
            ->method('getAdditionalInformation')
            ->with(Info::FRAUD_FILTERS)
            ->willReturn(
                [
                    'Total Purchase Price Ceiling' => 'Existing fraud message',
                    'RESPMSG' => 'Existing fraud message'
                ]
            );

        $this->paypalInfoManagerMock->expects($this->once())
            ->method('importToPayment')
            ->with(
                [
                    Info::FRAUD_FILTERS => [
                        'RESPMSG' => 'Existing fraud message',
                        'Total Purchase Price Ceiling' => 'Existing fraud message'
                    ]
                ]
            );

        $this->fraudHandler->handle($this->paymentMock, $this->responseMock);
    }
}
