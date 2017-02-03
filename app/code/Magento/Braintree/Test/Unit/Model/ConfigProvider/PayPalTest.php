<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Unit\Model\ConfigProvider;

use Magento\Braintree\Model\PaymentMethod;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class PayPalTest
 */
class PayPalTest extends \PHPUnit_Framework_TestCase
{
    const CLIENT_TOKEN = 'token';

    /**
     * @var \Magento\Braintree\Model\ConfigProvider\PayPal
     */
    protected $model;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Braintree\Model\Config\PayPal|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localResolverMock;

    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder('\Magento\Braintree\Model\Config\PayPal')
            ->disableOriginalConstructor()
            ->getMock();
        $this->localResolverMock = $this->getMock('\Magento\Framework\Locale\ResolverInterface');
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            'Magento\Braintree\Model\ConfigProvider\PayPal',
            [
                'config' => $this->configMock,
                'localeResolver' => $this->localResolverMock,
            ]
        );
    }

    /**
     * @dataProvider getConfigDataProvider
     */
    public function testGetConfig(
        $configData,
        $locale,
        $expectedResult
    ) {
        foreach ($configData as $key => $value) {
            $this->configMock->expects($this->any())
                ->method($key)
                ->willReturn($value);
        }

        $this->localResolverMock->expects($this->any())
            ->method('getLocale')
            ->willReturn($locale);

        $this->assertEquals($expectedResult, $this->model->getConfig());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getConfigDataProvider()
    {
        return [
            'not_active' => [
                'config_data' => [
                    'isActive' => false,
                    'getClientToken' => self::CLIENT_TOKEN,
                ],
                'locale' => 'en_US',
                'expected_result' => [
                ]
            ],
            'active' => [
                'config_data' => [
                    'isActive' => true,
                    'getClientToken' => self::CLIENT_TOKEN,
                    'getMerchantNameOverride' => 'merchantName',
                ],
                'locale' => 'en_US',
                'expected_result' => [
                    'payment' => [
                        'braintree_paypal' => [
                            'clientToken' => self::CLIENT_TOKEN,
                            'locale' => 'en_US',
                            'merchantDisplayName' => 'merchantName',
                        ],
                    ]
                ]
            ],
        ];
    }
}
