<?php
/**
 * Config helper Unit tests.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Class implements tests for \Magento\Webapi\Model\Soap\Config class.
 */
namespace Magento\Webapi\Model\Soap;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Webapi\Model\Soap\Config */
    protected $_soapConfig;

    /** @var \Magento\Webapi\Model\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $_configMock;

    /** @var \Magento\Webapi\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $_helperMock;

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
            'Magento\Webapi\Model\Config\ClassReflector',
            ['reflectClassMethods'],
            [],
            '',
            false
        );
        $classReflection->expects($this->any())->method('reflectClassMethods')->will($this->returnValue([]));
        $this->_helperMock = $this->getMock('Magento\Webapi\Helper\Data', [], [], '', false);
        $this->_configMock = $this->getMock('Magento\Webapi\Model\Config', [], [], '', false);
        $servicesConfig = [
            'services' => [
                'Magento\Framework\Module\Service\FooV1Interface' => [
                    'someMethod' => [
                        'resources' => [
                            [
                                'Magento_TestModule1::resource1',
                            ],
                        ],
                        'secure' => false,
                    ],
                ],
                'Magento\Framework\Module\Service\BarV1Interface' => [
                    'someMethod' => [
                        'resources' => [
                            [
                                'Magento_TestModule1::resource2',
                            ],
                        ],
                        'secure' => false,
                    ],
                ],
            ],
        ];

        $this->_configMock->expects($this->once())->method('getServices')->will($this->returnValue($servicesConfig));
        $this->_helperMock->expects(
            $this->any()
        )->method(
            'getServiceName'
        )->will(
            $this->returnValueMap(
                [
                    ['Magento\Framework\Module\Service\FooV1Interface', true, 'moduleFooV1'],
                    ['Magento\Framework\Module\Service\BarV1Interface', true, 'moduleBarV1'],
                ]
            )
        );
        $this->_soapConfig = new \Magento\Webapi\Model\Soap\Config(
            $objectManagerMock,
            $fileSystemMock,
            $this->_configMock,
            $classReflection,
            $this->_helperMock
        );
        parent::setUp();
    }

    public function testGetRequestedSoapServices()
    {
        $expectedResult = [
            [
                'methods' => [
                    'someMethod' => [
                        'method' => 'someMethod',
                        'inputRequired' => '',
                        'isSecure' => '',
                        'resources' => [['Magento_TestModule1::resource1']],
                    ],
                ],
                'class' => 'Magento\Framework\Module\Service\FooV1Interface',
            ],
        ];
        $result = $this->_soapConfig->getRequestedSoapServices(['moduleFooV1', 'moduleBarV2', 'moduleBazV1']);
        $this->assertEquals($expectedResult, $result);
    }

    public function testGetServiceMethodInfo()
    {
        $expectedResult = [
            'class' => 'Magento\Framework\Module\Service\BarV1Interface',
            'method' => 'someMethod',
            'isSecure' => false,
            'resources' => [['Magento_TestModule1::resource2']],
        ];
        $methodInfo = $this->_soapConfig->getServiceMethodInfo(
            'moduleBarV1SomeMethod',
            ['moduleBarV1', 'moduleBazV1']
        );
        $this->assertEquals($expectedResult, $methodInfo);
    }

    public function testGetSoapOperation()
    {
        $expectedResult = 'moduleFooV1SomeMethod';
        $soapOperation = $this->_soapConfig
            ->getSoapOperation('Magento\Framework\Module\Service\FooV1Interface', 'someMethod');
        $this->assertEquals($expectedResult, $soapOperation);
    }
}

require_once realpath(__DIR__ . '/../../_files/test_interfaces.php');
