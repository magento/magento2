<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model\Payflow\Service\Response\Validator;

use Magento\Framework\DataObject;
use Magento\Payment\Model\Method\ConfigInterface;
use Magento\Paypal\Model\Payflow\Service\Response\Validator\AVSResponse;
use Magento\Paypal\Model\Payflow\Transparent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AVSResponseTest extends TestCase
{
    /**
     * @var AVSResponse
     */
    private $validator;

    /**
     * @var ConfigInterface|MockObject
     */
    private $config;

    /**
     * @var Transparent|MockObject
     */
    private $payflowproFacade;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->config = $this->getMockBuilder(ConfigInterface::class)
            ->getMockForAbstractClass();

        $this->payflowproFacade = $this->getMockBuilder(Transparent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->validator = new AVSResponse();
    }

    /**
     * @param bool $expectedResult
     * @param DataObject $response
     * @param array $configMap
     *
     * @dataProvider validationDataProvider
     */
    public function testValidation(
        $expectedResult,
        DataObject $response,
        array $configMap
    ) {
        $this->payflowproFacade->method('getConfig')
            ->willReturn($this->config);

        $this->config->method('getValue')
            ->willReturnMap($configMap);

        static::assertEquals($expectedResult, $this->validator->validate($response, $this->payflowproFacade));

        if (!$expectedResult) {
            static::assertNotEmpty($response->getRespmsg());
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
                'response' => new DataObject(
                    [
                        'avsaddr' => 'Y',
                        'avszip' => 'Y',
                    ]
                ),
                'configMap' => [
                    ['avs_street', null, '0'],
                    ['avs_zip', null, '0'],
                ],
            ],
            [
                'expectedResult' => true,
                'response' => new DataObject(
                    [
                        'avsaddr' => 'Y',
                        'avszip' => 'Y',
                    ]
                ),
                'configMap' => [
                    ['avs_street', null, '1'],
                    ['avs_zip', null, '1'],
                ],
            ],
            [
                'expectedResult' => false,
                'response' => new DataObject(
                    [
                        'avsaddr' => 'Y',
                        'avszip' => 'N',
                    ]
                ),
                'configMap' => [
                    ['avs_street', null, '1'],
                    ['avs_zip', null, '1'],
                ],
            ],
            [
                'expectedResult' => true,
                'response' => new DataObject(
                    [
                        'avsaddr' => 'Y',
                        'avszip' => 'N',
                    ]
                ),
                'configMap' => [
                    ['avs_street', null, '1'],
                    ['avs_zip', null, '0'],
                ],
            ],
            [
                'expectedResult' => true,
                'response' => new DataObject(
                    [
                        'avsaddr' => 'Y',
                        'avszip' => 'N',
                    ]
                ),
                'configMap' => [
                    ['avs_street', null, '0'],
                    ['avs_zip', null, '0'],
                ],
            ],
            [
                'expectedResult' => true,
                'response' => new DataObject(
                    [
                        'avsaddr' => 'X',
                        'avszip' => 'Y',
                    ]
                ),
                'configMap' => [
                    ['avs_street', null, '1'],
                    ['avs_zip', null, '1'],
                ],
            ],
            [
                'expectedResult' => true,
                'response' => new DataObject(
                    [
                        'avsaddr' => 'X',
                        'avszip' => 'Y',
                    ]
                ),
                'configMap' => [
                    ['avs_street', null, '1'],
                    ['avs_zip', null, '0'],
                ],
            ],
        ];
    }
}
