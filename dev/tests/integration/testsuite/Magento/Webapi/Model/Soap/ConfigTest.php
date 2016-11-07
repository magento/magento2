<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Model\Soap;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    private $soapConfig;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->soapConfig = $objectManager->create(Config::class);
    }

    public function testGetRequestedSoapServices()
    {
        $expected = [
            'customerAccountManagementV1' => [
                'methods' => [
                    'activate' => [
                        'method' => 'activate',
                        'inputRequired' => false,
                        'isSecure' => false,
                        'resources' => [
                            'Magento_Customer::manage'
                        ],
                        'documentation'
                            => 'Activate a customer account using a key that was sent in a confirmation email.',
                        'interface' => [
                            'in' => [
                                'parameters' => [
                                    'email' => [
                                        'type' => 'string',
                                        'required' => true,
                                        'documentation' => null
                                    ],
                                    'confirmationKey' => [
                                        'type' => 'string',
                                        'required' => true,
                                        'documentation' => null
                                    ]
                                ]
                            ],
                            'out' => [
                                'parameters' => [
                                    'result' => [
                                        'type' => 'CustomerDataCustomerInterface',
                                        'required' => true,
                                        'documentation' => null
                                    ]
                                ],
                                'throws' => [
                                    '\\' . LocalizedException::class
                                ]
                            ]
                        ]
                    ]
                ],
                'class' => AccountManagementInterface::class,
                'description' => 'Interface for managing customers accounts.',
            ]
        ];
        $actual = $this->soapConfig->getRequestedSoapServices(
            [
                'customerAccountManagementV1',
                'NonExistentService'
            ]
        );
        $this->assertEquals(array_replace_recursive($actual, $expected), $actual);
    }

    public function testGetServiceMethodInfo()
    {
        $expected = [
            'class' => CustomerRepositoryInterface::class,
            'method' => 'getById',
            'isSecure' => false,
            'resources' => [
                'Magento_Customer::customer',
                'self'
            ],
        ];
        $actual = $this->soapConfig->getServiceMethodInfo(
            'customerCustomerRepositoryV1GetById',
            [
                'customerCustomerRepositoryV1',
                'NonExistentService'
            ]
        );
        $this->assertEquals($expected, $actual);
    }

    public function testGetSoapOperation()
    {
        $expected = 'customerAccountManagementV1Activate';
        $actual = $this->soapConfig
            ->getSoapOperation(
                AccountManagementInterface::class,
                'activate',
                'V1'
            );
        $this->assertEquals($expected, $actual);
    }
}
