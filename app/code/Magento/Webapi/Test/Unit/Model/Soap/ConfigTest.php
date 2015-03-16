<?php
/**
 * Config helper Unit tests.
 *
 * Copyright © 2015 Magento. All rights reserved.
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

    /**
     * Set up helper.
     */
    protected function setUp()
    {
        $objectManagerMock = $this->getMockBuilder(
            'Magento\Framework\App\ObjectManager'
        )->disableOriginalConstructor()->getMock();
        $fileSystemMock = $this->getMockBuilder('Magento\Framework\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
        $classReflection = $this->getMock(
            'Magento\Webapi\Model\Soap\Config\ClassReflector',
            ['reflectClassMethods'],
            [],
            '',
            false
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
         * @var $cacheMock \Magento\Framework\App\Cache\Type\Webapi
         */
        $cacheMock = $this->getMockBuilder('Magento\Framework\App\Cache\Type\Webapi')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var $readerMock \Magento\Webapi\Model\Config\Reader */
        $readerMock = $this->getMockBuilder('Magento\Webapi\Model\Config\Reader')
            ->disableOriginalConstructor()
            ->getMock();
        $readerMock->expects($this->once())->method('read')->will($this->returnValue($servicesConfig));

        /** @var $config \Magento\Webapi\Model\Config */
        $config = new \Magento\Webapi\Model\Config($cacheMock, $readerMock);

        $this->_soapConfig = new \Magento\Webapi\Model\Soap\Config(
            $objectManagerMock,
            $fileSystemMock,
            $config,
            $classReflection,
            $cacheMock,
            $registryMock
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

    /**
     * Test identifying service name including subservices using class name.
     *
     * @dataProvider serviceNameDataProvider
     */
    public function testGetServiceName($className, $version, $preserveVersion, $expected)
    {
        $actual = $this->_soapConfig->getServiceName($className, $version, $preserveVersion);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Dataprovider for testGetServiceName
     *
     * @return string
     */
    public function serviceNameDataProvider()
    {
        return [
            ['Magento\Customer\Api\AccountManagementInterface', 'V1', false, 'customerAccountManagement'],
            ['Magento\Customer\Api\AddressRepositoryInterface', 'V1', true, 'customerAddressRepositoryV1'],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider dataProviderForTestGetServiceNameInvalidName
     */
    public function testGetServiceNameInvalidName($interfaceClassName, $version)
    {
        $this->_soapConfig->getServiceName($interfaceClassName, $version);
    }

    /**
     * Dataprovider for testGetServiceNameInvalidName
     *
     * @return string
     */
    public function dataProviderForTestGetServiceNameInvalidName()
    {
        return [
            ['BarV1Interface', 'V1'], // Missed vendor, module, 'Service'
            ['Service\\V1Interface', 'V1'], // Missed vendor and module
            ['Magento\\Foo\\Service\\BarVxInterface', 'V1'], // Version number should be a number
            ['Magento\\Foo\\Service\\BarInterface', 'V1'], // Version missed
            ['Magento\\Foo\\Service\\BarV1', 'V1'], // 'Interface' missed
            ['Foo\\Service\\BarV1Interface', 'V1'], // Module missed
            ['Foo\\BarV1Interface', 'V1'] // Module and 'Service' missed
        ];
    }

    public function testGetServiceMetadata()
    {
        $expectedResult = [
            'methods' => [
                'activate' => [
                    'method' => 'activate',
                    'inputRequired' => '',
                    'isSecure' => '',
                    'resources' => [['Magento_Customer::manage']],
                ],
            ],
            'class' => 'Magento\Customer\Api\AccountManagementInterface',
        ];
        $result = $this->_soapConfig->getServiceMetadata('customerAccountManagementV1');
        $this->assertEquals($expectedResult, $result);

    }
}

require_once realpath(__DIR__ . '/../../_files/test_interfaces.php');
