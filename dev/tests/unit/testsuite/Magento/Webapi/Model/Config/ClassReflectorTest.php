<?php
/**
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
namespace Magento\Webapi\Model\Config;

/**
 * Test for class reflector.
 */
class ClassReflectorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Webapi\Model\Config\ClassReflector\TypeProcessor|\PHPUnit_Framework_MockObject_MockObject */
    protected $_typeProcessor;

    /** @var \Magento\Webapi\Model\Config\ClassReflector */
    protected $_classReflector;

    /**
     * Set up helper.
     */
    protected function setUp()
    {
        $this->_typeProcessor = $this->getMock(
            '\Magento\Webapi\Model\Config\ClassReflector\TypeProcessor',
            array('process'),
            array(),
            '',
            false
        );
        $this->_typeProcessor->expects(
            $this->any()
        )->method(
            'process'
        )->will(
            $this->returnValueMap(array(array('string', 'str'), array('int', 'int')))
        );
        $this->_classReflector = new \Magento\Webapi\Model\Config\ClassReflector($this->_typeProcessor);
    }

    public function testReflectClassMethods()
    {
        $data = $this->_classReflector->reflectClassMethods(
            '\\Magento\\Webapi\\Model\\Config\\TestServiceForClassReflector',
            array('generateRandomString' => array('method' => 'generateRandomString'))
        );
        $this->assertEquals(array('generateRandomString' => $this->_getSampleReflectionData()), $data);
    }

    public function testExtractMethodData()
    {
        $classReflection = new \Zend\Server\Reflection\ReflectionClass(
            new \ReflectionClass('\\Magento\\Webapi\\Model\\Config\\TestServiceForClassReflector')
        );
        /** @var $methodReflection ReflectionMethod */
        $methodReflection = $classReflection->getMethods()[0];
        $methodData = $this->_classReflector->extractMethodData($methodReflection);
        $expectedResponse = $this->_getSampleReflectionData();
        $this->assertEquals($expectedResponse, $methodData);
    }

    /**
     * Expected reflection data for TestServiceForClassReflector generateRandomString method
     *
     * @return array
     */
    protected function _getSampleReflectionData()
    {
        return array(
            'documentation' => 'Basic random string generator',
            'interface' => array(
                'in' => array(
                    'parameters' => array(
                        'length' => array(
                            'type' => 'int',
                            'required' => true,
                            'documentation' => 'length of the random string'
                        )
                    )
                ),
                'out' => array(
                    'parameters' => array(
                        'result' => array('type' => 'str', 'documentation' => 'random string', 'required' => true)
                    )
                )
            )
        );
    }
}
