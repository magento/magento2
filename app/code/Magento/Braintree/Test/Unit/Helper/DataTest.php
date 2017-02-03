<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Model\Config as PaymentConfig;

/**
 * Test for Data
 */
class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Braintree\Helper\Data
     */
    private $model;

    /**
     * @var PaymentConfig
     */
    private $paymentConfig;

    /**
     * @var \Magento\Braintree\Model\Config\Cc
     */
    private $braintreeCcConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    private $dateFormat;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * test setup
     */
    public function setUp()
    {

        $this->paymentConfig = $this->getMockBuilder('\Magento\Payment\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->braintreeCcConfig = $this->getMockBuilder('\Magento\Braintree\Model\Config\Cc')
            ->disableOriginalConstructor()
            ->getMock();

        $this->dateFormat = $this->getMockBuilder('\Magento\Framework\Stdlib\DateTime')
            ->disableOriginalConstructor()
            ->getMock();

        $this->dateTime = $this->getMockBuilder('\Magento\Framework\Stdlib\DateTime\DateTime')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = new ObjectManager($this);
        $this->model = $this->objectManager->getObject(
            'Magento\Braintree\Helper\Data',
            [
                'paymentConfig' =>  $this->paymentConfig,
                'braintreeCcConfig' =>  $this->braintreeCcConfig,
                'dateFormat' =>  $this->dateFormat,
                'dateTime' =>  $this->dateTime,
            ]
        );
    }

    /**
     * @param string $name
     * @param array $cctypes
     * @param boolean|string $expected
     * @dataProvider getCcTypeCodeByNameDataProvider
     */
    public function testGetCcTypeCodeByName($name, $cctypes, $expected)
    {

        $this->paymentConfig->expects($this->any())
            ->method('getCcTypes')
            ->willReturn($cctypes);


        $result = $this->model->GetCcTypeCodeByName($name);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getCcTypeCodeByNameDataProvider()
    {
        return [
                [
                    'name' => 'DISCOVER',
                    'cctypes' => [
                        'VI' => 'VISA',
                        'MC' => 'MASTERCARD',
                    ],
                    'expected' => false,
                ],
                [
                    'name' => 'VISA',
                    'cctypes' => [
                        'VI' => 'VISA',
                        'MC' => 'MASTERCARD',
                    ],
                    'expected' => 'VI',
                ],
            ];

    }

    /**
     * @param string $name
     * @param array $cctypes
     * @param boolean|string $expected
     * @dataProvider getCcTypeNameByCodeDataProvider
     */
    public function testGetCcTypeNameByCode($name, $cctypes, $expected)
    {

        $this->paymentConfig->expects($this->any())
            ->method('getCcTypes')
            ->willReturn($cctypes);


        $result = $this->model->getCcTypeNameByCode($name);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getCcTypeNameByCodeDataProvider()
    {
        return [
            [
                'name' => 'DI',
                'cctypes' => [
                    'VI' => 'VISA',
                    'MC' => 'MASTERCARD',
                ],
                'expected' => false,
            ],
            [
                'name' => 'VI',
                'cctypes' => [
                    'VI' => 'VISA',
                    'MC' => 'MASTERCARD',
                ],
                'expected' => 'VISA',
            ],
        ];

    }

    /**
     * @param array $cctypes
     * @param boolean|string $expected
     * @dataProvider getCcTypesDataProvider
     */
    public function testGetCcTypes($cctypes, $expected)
    {
        $this->paymentConfig->expects($this->any())
            ->method('getCcTypes')
            ->willReturn($cctypes);

        $result = $this->model->getCcTypes();
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getCcTypesDataProvider()
    {
        return [
            [
                'cctypes' => null,
                'expected' => false,
            ],
            [
                'cctypes' => [
                    'VI' => 'VISA',
                    'MC' => 'MASTERCARD1',
                ],
                'expected' => [
                    'VI' => 'VISA',
                    'MC' => 'MASTERCARD1',
                ],
            ],
        ];

    }

    public function testGenerateCustomerId()
    {
        $result = $this->model->generateCustomerId(1, "email@email.com");
        $this->assertEquals(md5("1" . '-' . "email@email.com"), $result);
    }


    public function testClearTransactionId()
    {
        $result = $this->model->clearTransactionId(1);
        $this->assertEquals(1, $result);
        $result = $this->model->clearTransactionId("1-".\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE);
        $this->assertEquals(1, $result);
        $result = $this->model->clearTransactionId("1-".\Magento\Sales\Model\Order\Payment\Transaction::TYPE_VOID);
        $this->assertEquals(1, $result);
    }

    /**
     * @param array $data
     * @param array $expected
     * @dataProvider getCcAvailableCardTypesDataProvider
     */
    public function testGetCcAvailableCardTypes($data, $expected)
    {
        $this->braintreeCcConfig->expects($this->any())
            ->method('getConfigData')
            ->willReturn($data['cctypes']);

        $this->braintreeCcConfig->expects($this->any())
            ->method('getCountrySpecificCardTypeConfig')
            ->willReturn($data['cctypesCountrySpecific']);

        $this->braintreeCcConfig->expects($this->any())
            ->method('getApplicableCardTypes')
            ->willReturn($data['applicableCards']);

        $this->paymentConfig->expects($this->any())
            ->method('getCcTypes')
            ->willReturn($data['ccTypes']);



        $result = $this->model->getCcAvailableCardTypes($data['country']);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getCcAvailableCardTypesDataProvider()
    {
        return [
            [
                'data' => [
                    'cctypes' => 'AE,MC',
                    'cctypesCountrySpecific' => [
                        'US'=> [
                            'VI'
                        ]
                    ],
                    'applicableCards' => ['VI', 'MC'],
                    'country' => 'US',
                    'ccTypes' => [
                        'AE' => 'American Express',
                        'VI' => 'Visa',
                        'MC' => 'MasterCard',
                    ],
                ],
                'expected' => [
                    'VI' => 'Visa',
                    'MC' => 'MasterCard',
                ],
            ],
            [
                'data' => [
                    'cctypes' => 'AE,MC',
                    'cctypesCountrySpecific' => [
                        'US'=> [
                            'VI'
                        ]
                    ],
                    'applicableCards' => ['VI', 'MC'],
                    'country' => null,
                    'ccTypes' => [
                        'AE' => 'American Express',
                        'VI' => 'Visa',
                        'MC' => 'MasterCard',
                    ],
                ],
                'expected' => [
                    'AE' => 'American Express',
                    'VI' => 'Visa',
                    'MC' => 'MasterCard',
                ],
            ],
            [
                'data' => [
                    'cctypes' => 'AE,MC',
                    'cctypesCountrySpecific' => [
                        'US'=> [
                            'VI'
                        ]
                    ],
                    'applicableCards' => ['VI', 'MC'],
                    'country' => 'AG',
                    'ccTypes' => [
                        'AE' => 'American Express',
                        'VI' => 'Visa',
                        'MC' => 'MasterCard',
                    ],
                ],
                'expected' => [
                    'VI' => 'Visa',
                    'MC' => 'MasterCard',
                ],
            ],
            [
                'data' => [
                    'cctypes' => 'AE,MC',
                    'cctypesCountrySpecific' => [
                        'AG'=> [
                            'VI'
                        ]
                    ],
                    'applicableCards' => ['VI'],
                    'country' => 'AG',
                    'ccTypes' => [
                        'AE' => 'American Express',
                        'VI' => 'Visa',
                        'MC' => 'MasterCard',
                    ],
                ],
                'expected' => [
                    'VI' => 'Visa',
                ],
            ],
            [
                'data' => [
                    'cctypes' => 'AE,MC',
                    'cctypesCountrySpecific' => [],
                    'applicableCards' => ['VI'],
                    'country' => null,
                    'ccTypes' => [
                        'AE' => 'American Express',
                        'VI' => 'Visa',
                        'MC' => 'MasterCard',
                    ],
                ],
                'expected' => [
                    'AE' => 'American Express',
                    'MC' => 'MasterCard',
                ],
            ],
        ];

    }
}
