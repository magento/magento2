<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model\Payflow\Service\Response\Validator;

use Magento\Payment\Model\Method\ConfigInterface;
use Magento\Paypal\Model\Payflow\Service\Response\Validator\AVSResponse;
use Magento\Paypal\Model\Payflow\Transparent;

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
     * @var ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var Transparent|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $payflowproFacade;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder(ConfigInterface::class)
            ->getMockForAbstractClass();

        $this->payflowproFacade = $this->getMockBuilder(Transparent::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->validator = new AVSResponse();
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
        $this->payflowproFacade->expects(static::once())
            ->method('getConfig')
            ->willReturn($this->configMock);

        $this->configMock->expects(static::exactly($exactlyCount))
            ->method('getValue')
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
