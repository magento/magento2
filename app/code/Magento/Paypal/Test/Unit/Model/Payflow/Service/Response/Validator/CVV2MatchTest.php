<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model\Payflow\Service\Response\Validator;

use Magento\Paypal\Model\Payflow\Service\Response\Validator\CVV2Match;
use Magento\Paypal\Model\Payflow\Transparent;
use Magento\Payment\Model\Method\ConfigInterface;

/**
 * Class CVV2MatchTest
 *
 * Test class for \Magento\Paypal\Model\Payflow\Service\Response\Validator\CVV2Match
 */
class CVV2MatchTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CVV2Match
     */
    protected $validator;

    /**
     * @var \Magento\Payment\Model\Method\ConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $configMock;

    /**
     * @var Transparent|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $payflowproFacade;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->configMock = $this->getMockBuilder(ConfigInterface::class)
            ->getMockForAbstractClass();
        $this->payflowproFacade = $this->getMockBuilder(Transparent::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->validator = new CVV2Match();
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
        $this->payflowproFacade->expects(static::once())
            ->method('getConfig')
            ->willReturn($this->configMock);

        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with(CVV2Match::CONFIG_NAME)
            ->willReturn($avsSecurityCodeFlag);

        $this->assertEquals($expectedResult, $this->validator->validate($response, $this->payflowproFacade));

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
