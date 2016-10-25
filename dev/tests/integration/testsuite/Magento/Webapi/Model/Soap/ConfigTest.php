<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Model\Soap;

// @codingStandardsIgnoreFile

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Webapi\Model\Soap\Config
     */
    private $_soapConfig;

    /**
     * @var \Magento\TestFramework\Helper\Bootstrap
     */
    private $objectManager;

    /**
     * Set up helper.
     */
    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $typeProcessor = $this->objectManager->create(\Magento\Framework\Reflection\TypeProcessor::class);

        $objectManagerMock = $this->getMockBuilder(
            \Magento\Framework\App\ObjectManager::class
        )->disableOriginalConstructor()->getMock();

        $classReflection = $this->getMock(
            \Magento\Webapi\Model\Config\ClassReflector::class,
            ['reflectClassMethods'],
            ['_typeProcessor' => $typeProcessor],
            ''
        );
        $classReflection->expects($this->any())->method('reflectClassMethods')->will($this->returnValue([]));

        $servicesConfig = [
            'services' => [\Magento\Customer\Api\AccountManagementInterface::class => [
                    'V1' => [
                        'methods' => [
                            'activate' => [
                                'resources' => [
                                    [
                                        'Magento_Customer::manage',
                                    ],
                                ],
                                'secure' => false,
                            ],
                        ],
                    ],
                ], \Magento\Customer\Api\CustomerRepositoryInterface::class => [
                    'V1' => [
                        'methods' => [
                            'getById' => [
                                'resources' => [
                                    [
                                        'Magento_Customer::customer',
                                    ],
                                ],
                                'secure' => false,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        /**
         * @var $registryMock \Magento\Framework\Registry
         */
        $registryMock = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        /**
         * @var $cacheMock \Magento\Webapi\Model\Cache\Type\Webapi
         */
        $cacheMock = $this->getMockBuilder(\Magento\Webapi\Model\Cache\Type\Webapi::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var $readerMock \Magento\Webapi\Model\Config\Reader */
        $readerMock = $this->getMockBuilder(\Magento\Webapi\Model\Config\Reader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $readerMock->expects($this->any())->method('read')->will($this->returnValue($servicesConfig));

        /** @var $config \Magento\Webapi\Model\Config */
        $config = new \Magento\Webapi\Model\Config($cacheMock, $readerMock);

        /** @var $config \Magento\Webapi\Model\ServiceMetadata */
        $serviceMetadata = $this->objectManager->create(
            \Magento\Webapi\Model\ServiceMetadata::class,
            [
                'config' => $config,
                'cache' => $cacheMock,
                'classReflector' => $classReflection,
                'typeProcessor' => $typeProcessor
            ]
        );

        $this->_soapConfig = $this->objectManager->create(
            \Magento\Webapi\Model\Soap\Config::class,
            [
                'objectManager' => $objectManagerMock,
                'registry' => $registryMock,
                'serviceMetadata' => $serviceMetadata,
            ]
        );
    }

    public function testGetRequestedSoapServices()
    {
        $expectedResult = [
            'customerAccountManagementV1' =>
                [
                    'methods' => [
                        'activate' => [
                            'method' => 'activate',
                            'inputRequired' => '',
                            'isSecure' => '',
                            'resources' => [['Magento_Customer::manage']],
                        ],
                    ],
                    'class' => \Magento\Customer\Api\AccountManagementInterface::class,
                    'description' => 'Interface for managing customers accounts.',
                ],
        ];

        $result = $this->_soapConfig->getRequestedSoapServices(
            ['customerAccountManagementV1', 'moduleBarV2', 'moduleBazV1']
        );

        $this->assertEquals($expectedResult, $result);
    }

    public function testGetServiceMethodInfo()
    {
        $expectedResult = [
            'class' => \Magento\Customer\Api\CustomerRepositoryInterface::class,
            'method' => 'getById',
            'isSecure' => false,
            'resources' => [['Magento_Customer::customer']],
        ];
        $methodInfo = $this->_soapConfig->getServiceMethodInfo(
            'customerCustomerRepositoryV1GetById',
            ['customerCustomerRepositoryV1', 'moduleBazV1']
        );
        $this->assertEquals($expectedResult, $methodInfo);
    }

    public function testGetSoapOperation()
    {
        $expectedResult = 'customerAccountManagementV1Activate';
        $soapOperation = $this->_soapConfig
            ->getSoapOperation(\Magento\Customer\Api\AccountManagementInterface::class, 'activate', 'V1');
        $this->assertEquals($expectedResult, $soapOperation);
    }
}
