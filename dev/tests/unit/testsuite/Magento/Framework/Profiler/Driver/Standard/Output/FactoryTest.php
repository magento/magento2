<?php
/**
 * Test class for \Magento\Framework\Profiler\Driver\Standard\Output\Factory
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
namespace Magento\Framework\Profiler\Driver\Standard\Output;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Profiler\Driver\Standard\Output\Factory
     */
    protected $_factory;

    /**
     * @var string
     */
    protected $_defaultOutputPrefix = 'Magento_Framework_Profiler_Driver_Standard_Output_Test_';

    /**
     * @var string
     */
    protected $_defaultOutputType = 'default';

    protected function setUp()
    {
        $this->_factory = new \Magento\Framework\Profiler\Driver\Standard\Output\Factory(
            $this->_defaultOutputPrefix,
            $this->_defaultOutputType
        );
    }

    public function testConstructor()
    {
        $this->assertAttributeEquals($this->_defaultOutputPrefix, '_defaultOutputPrefix', $this->_factory);
        $this->assertAttributeEquals($this->_defaultOutputType, '_defaultOutputType', $this->_factory);
    }

    public function testDefaultConstructor()
    {
        $factory = new \Magento\Framework\Profiler\Driver\Standard\Output\Factory();
        $this->assertAttributeNotEmpty('_defaultOutputPrefix', $factory);
        $this->assertAttributeNotEmpty('_defaultOutputType', $factory);
    }

    /**
     * @dataProvider createDataProvider
     * @param array $configData
     * @param string $expectedClass
     */
    public function testCreate($configData, $expectedClass)
    {
        $driver = $this->_factory->create($configData);
        $this->assertInstanceOf($expectedClass, $driver);
        $this->assertInstanceOf('Magento\Framework\Profiler\Driver\Standard\OutputInterface', $driver);
    }

    /**
     * @return array
     */
    public function createDataProvider()
    {
        $defaultOutputClass = $this->getMockClass(
            'Magento\Framework\Profiler\Driver\Standard\OutputInterface',
            array(),
            array(),
            'Magento_Framework_Profiler_Driver_Standard_Output_Test_Default'
        );
        $testOutputClass = $this->getMockClass(
            'Magento\Framework\Profiler\Driver\Standard\OutputInterface',
            array(),
            array(),
            'Magento_Framework_Profiler_Driver_Standard_Output_Test_Test'
        );
        return array(
            'Prefix and concrete type' => array(array('type' => 'test'), $testOutputClass),
            'Prefix and default type' => array(array(), $defaultOutputClass),
            'Concrete class' => array(array('type' => $testOutputClass), $testOutputClass)
        );
    }

    public function testCreateUndefinedClass()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            sprintf(
                'Cannot create standard driver output, class "%s" doesn\'t exist.',
                'Magento_Framework_Profiler_Driver_Standard_Output_Test_Baz'
            )
        );
        $this->_factory->create(array('type' => 'baz'));
    }

    public function testCreateInvalidClass()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Output class "stdClass" must implement \Magento\Framework\Profiler\Driver\Standard\OutputInterface.'
        );
        $this->_factory->create(array('type' => 'stdClass'));
    }
}
