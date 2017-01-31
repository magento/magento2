<?php
/**
 * Config helper Unit tests.
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * Class implements tests for \Magento\Webapi\Model\Soap\Config class.
 */
namespace Magento\Webapi\Test\Unit\Model\Soap;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Webapi\Model\Soap\Config */
    protected $_soapConfig;

    /** @var  \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    protected $objectManager;

    /**
     * Set up helper.
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $typeProcessor = $this->objectManager->getObject('Magento\Framework\Reflection\TypeProcessor');

        $objectManagerMock = $this->getMockBuilder(
            'Magento\Framework\App\ObjectManager'
        )->disableOriginalConstructor()->getMock();

        $classReflection = $this->getMock(
            'Magento\Webapi\Model\Config\ClassReflector',
            ['reflectClassMethods'],
            ['_typeProcessor' => $typeProcessor],
            ''
        );
        $classReflection->expects($this->any())->method('reflectClassMethods')->will($this->returnValue([]));

        $servicesConfig = [
            'services' => [
                'Magento\Customer\Api\AccountManagementInterface' => [
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
                ],
                'Magento\Customer\Api\CustomerRepositoryInterface' => [
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
        $registryMock = $this->getMockBuilder('Magento\Framework\Registry')
            ->disableOriginalConstructor()
            ->getMock();

        /**
         * @var $cacheMock \Magento\Webapi\Model\Cache\Type\Webapi
         */
        $cacheMock = $this->getMockBuilder('Magento\Webapi\Model\Cache\Type\Webapi')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var $readerMock \Magento\Webapi\Model\Config\Reader */
        $readerMock = $this->getMockBuilder('Magento\Webapi\Model\Config\Reader')
            ->disableOriginalConstructor()
            ->getMock();
        $readerMock->expects($this->any())->method('read')->will($this->returnValue($servicesConfig));

        /** @var $config \Magento\Webapi\Model\Config */
        $config = new \Magento\Webapi\Model\Config($cacheMock, $readerMock);

        /** @var $config \Magento\Webapi\Model\ServiceMetadata */
        $serviceMetadata = new \Magento\Webapi\Model\ServiceMetadata(
            $config,
            $cacheMock,
            $classReflection,
            $typeProcessor);

        $this->_soapConfig = $this->objectManager->getObject(
            'Magento\Webapi\Model\Soap\Config',
            [
                'objectManager' => $objectManagerMock,
                'registry' => $registryMock,
                'serviceMetadata' => $serviceMetadata,
            ]
        );
        parent::setUp();
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
                    'class' => 'Magento\Customer\Api\AccountManagementInterface',
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
            'class' => 'Magento\Customer\Api\CustomerRepositoryInterface',
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
            ->getSoapOperation('Magento\Customer\Api\AccountManagementInterface', 'activate', 'V1');
        $this->assertEquals($expectedResult, $soapOperation);
    }
}

require_once realpath(__DIR__ . '/../../_files/test_interfaces.php');
