<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Test\Unit\Model\Directpost;

use Magento\Authorizenet\Model\Directpost\Request;
use Magento\Framework\Intl\DateTimeFactory;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DateTimeFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTimeFactory;

    /**
     * @var Request
     */
    private $requestModel;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->dateTimeFactory = $this->getMockBuilder(DateTimeFactory::class)->disableOriginalConstructor()->getMock();
        $dateTime = new \DateTime('2016-07-05 00:00:00', new \DateTimeZone('UTC'));
        $this->dateTimeFactory->method('create')->willReturn($dateTime);

        $this->requestModel = new Request($this->dateTimeFactory);
    }

    /**
     * @param string $signatureKey
     * @param string $expectedHash
     * @return void
     * @dataProvider signRequestDataProvider
     */
    public function testSignRequestData($signatureKey, $expectedHash)
    {
        /** @var \Magento\Authorizenet\Model\Directpost|\PHPUnit_Framework_MockObject_MockObject $paymentMethod */
        $paymentMethod = $this->getMock(\Magento\Authorizenet\Model\Directpost::class, [], [], '', false);
        $paymentMethod->method('getConfigData')
            ->willReturnMap(
                [
                    ['test', null, true],
                    ['login', null, 'login'],
                    ['trans_key', null, 'trans_key'],
                    ['signature_key', null, $signatureKey],
                ]
            );

        $this->requestModel->setConstantData($paymentMethod);
        $this->requestModel->signRequestData();
        $signHash = $this->requestModel->getXFpHash();

        $this->assertEquals($expectedHash, $signHash);
    }

    /**
     * @return array
     */
    public function signRequestDataProvider()
    {
        return [
            [
                'signatureKey' => '3EAFCE5697C1B4B9748385C1FCD29D86F3B9B41C7EED85A3A01DFF65' .
                    '70C8C29373C2A153355C3313CDF4AF723C0036DBF244A0821713A910024EE85547CEF37F',
                'expectedHash' => '719ED94DF5CF3510CB5531E8115462C8F12CBCC8E917BD809E8D40B4FF06' .
                    '1E14953554403DD9813CCCE0F31B184EB4DEF558E9C0747505A0C25420372DB00BE1'
            ],
            [
                'signatureKey' => '',
                'expectedHash' => '3656211f2c41d1e4c083606f326c0460'
            ],
        ];
    }
}
