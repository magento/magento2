<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model\Payflow\Service\Response\Validator;

use Magento\Framework\DataObject;
use Magento\Payment\Model\Method\ConfigInterface;
use Magento\Paypal\Model\Payflow\Service\Response\Validator\CVV2Match;
use Magento\Paypal\Model\Payflow\Transparent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class CVV2MatchTest
 *
 * Test class for \Magento\Paypal\Model\Payflow\Service\Response\Validator\CVV2Match
 */
class CVV2MatchTest extends TestCase
{
    /**
     * @var CVV2Match
     */
    protected $validator;

    /**
     * @var ConfigInterface|MockObject
     */
    protected $configMock;

    /**
     * @var Transparent|MockObject
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
     * @param DataObject $response
     * @param string $avsSecurityCodeFlag
     *
     * @dataProvider validationDataProvider
     */
    public function testValidation(
        $expectedResult,
        DataObject $response,
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
                'response' => new DataObject(
                    [
                        'cvv2match' => 'Y',
                    ]
                ),
                'configValue' => '0',
            ],
            [
                'expectedResult' => true,
                'response' => new DataObject(
                    [
                        'cvv2match' => 'Y',
                    ]
                ),
                'configValue' => '1',
            ],
            [
                'expectedResult' => true,
                'response' => new DataObject(
                    [
                        'cvv2match' => 'X',
                    ]
                ),
                'configValue' => '1',
            ],
            [
                'expectedResult' => false,
                'response' => new DataObject(
                    [
                        'cvv2match' => 'N',
                    ]
                ),
                'configValue' => '1',
            ],
            [
                'expectedResult' => true,
                'response' => new DataObject(
                    [
                        'cvv2match' => null,
                    ]
                ),
                'configValue' => '1',
            ],
            [
                'expectedResult' => true,
                'response' => new DataObject(),
                'configValue' => '1',
            ],
        ];
    }
}
