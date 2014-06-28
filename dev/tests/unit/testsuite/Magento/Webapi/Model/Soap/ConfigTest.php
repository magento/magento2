<?php
/**
 * Config helper Unit tests.
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $fileSystemMock = $this->getMockBuilder('Magento\Framework\App\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
        $classReflection = $this->getMock(
            'Magento\Webapi\Model\Config\ClassReflector',
            array('reflectClassMethods'),
            array(),
            '',
            false
        );
        $classReflection->expects($this->any())->method('reflectClassMethods')->will($this->returnValue(array()));
        $this->_helperMock = $this->getMock('Magento\Webapi\Helper\Data', array(), array(), '', false);
        $this->_configMock = $this->getMock('Magento\Webapi\Model\Config', array(), array(), '', false);
        $servicesConfig = [
            'services' => [
                'Magento\Framework\Module\Service\FooV1Interface' => [
                    'someMethod' => [
                        'resources' => [
                            [
                                'Magento_TestModule1::resource1'
                            ]
                        ],
                        'secure' => false,
                    ],
                ],
                'Magento\Framework\Module\Service\BarV1Interface' => [
                    'someMethod' => [
                        'resources' => [
                            [
                                'Magento_TestModule1::resource2'
                            ]
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
                array(
                    array('Magento\Framework\Module\Service\FooV1Interface', true, 'moduleFooV1'),
                    array('Magento\Framework\Module\Service\BarV1Interface', true, 'moduleBarV1')
                )
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
        $expectedResult = array(
            array(
                'methods' => array(
                    'someMethod' => array(
                        'method' => 'someMethod',
                        'inputRequired' => '',
                        'isSecure' => '',
                        'resources' => array(array('Magento_TestModule1::resource1'))
                    )
                ),
                'class' => 'Magento\Framework\Module\Service\FooV1Interface'
            )
        );
        $result = $this->_soapConfig->getRequestedSoapServices(array('moduleFooV1', 'moduleBarV2', 'moduleBazV1'));
        $this->assertEquals($expectedResult, $result);
    }

    public function testGetServiceMethodInfo()
    {
        $expectedResult = array(
            'class' => 'Magento\Framework\Module\Service\BarV1Interface',
            'method' => 'someMethod',
            'isSecure' => false,
            'resources' => array(array('Magento_TestModule1::resource2'))
        );
        $methodInfo = $this->_soapConfig->getServiceMethodInfo(
            'moduleBarV1SomeMethod',
            array('moduleBarV1', 'moduleBazV1')
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
