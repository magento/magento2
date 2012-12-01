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
 * @category    Magento
 * @package     performance_tests
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magento_Performance_Scenario_Handler_FileFormatTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Performance_Scenario_Handler_FileFormat
     */
    protected $_object;

    /**
     * @var Magento_Performance_Scenario_HandlerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_handler;

    /**
     * @var Magento_Performance_Scenario
     */
    protected $_scenario;

    protected function setUp()
    {
        $this->_handler = $this->getMockForAbstractClass('Magento_Performance_Scenario_HandlerInterface');
        $this->_object = new Magento_Performance_Scenario_Handler_FileFormat();
        $this->_object->register('jmx', $this->_handler);
        $this->_scenario = new Magento_Performance_Scenario('Scenario', 'scenario.jmx', array(), array(), array());
    }

    protected function tearDown()
    {
        $this->_handler = null;
        $this->_object = null;
        $this->_scenario = null;
    }

    public function testRegisterGetHandler()
    {
        $this->assertNull($this->_object->getHandler('php'));
        $this->_object->register('php', $this->_handler);
        $this->assertSame($this->_handler, $this->_object->getHandler('php'));
    }

    public function testRunDelegation()
    {
        $reportFile = 'scenario.jtl';
        $this->_handler
            ->expects($this->once())
            ->method('run')
            ->with($this->_scenario, $reportFile)
        ;
        $this->_object->run($this->_scenario, $reportFile);
    }

    /**
     * @expectedException Magento_Exception
     * @expectedExceptionMessage Unable to run scenario 'Scenario', format is not supported.
     */
    public function testRunUnsupportedFormat()
    {
        $scenario = new Magento_Performance_Scenario('Scenario', 'scenario.txt', array(), array(), array());
        $this->_object->run($scenario);
    }
}
