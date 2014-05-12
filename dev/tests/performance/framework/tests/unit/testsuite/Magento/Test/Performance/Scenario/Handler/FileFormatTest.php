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
namespace Magento\Test\Performance\Scenario\Handler;

class FileFormatTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Performance\Scenario\Handler\FileFormat
     */
    protected $_object;

    /**
     * @var \Magento\TestFramework\Performance\Scenario\HandlerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_handler;

    /**
     * @var \Magento\TestFramework\Performance\Scenario
     */
    protected $_scenario;

    protected function setUp()
    {
        $this->_handler = $this->getMockForAbstractClass(
            'Magento\TestFramework\Performance\Scenario\HandlerInterface'
        );
        $this->_object = new \Magento\TestFramework\Performance\Scenario\Handler\FileFormat();
        $this->_object->register('jmx', $this->_handler);
        $this->_scenario = new \Magento\TestFramework\Performance\Scenario(
            'Scenario',
            'scenario.jmx',
            array(),
            array(),
            array()
        );
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
        $this->_handler->expects($this->once())->method('run')->with($this->_scenario, $reportFile);
        $this->_object->run($this->_scenario, $reportFile);
    }

    /**
     * @expectedException \Magento\Framework\Exception
     * @expectedExceptionMessage Unable to run scenario 'Scenario', format is not supported.
     */
    public function testRunUnsupportedFormat()
    {
        $scenario = new \Magento\TestFramework\Performance\Scenario(
            'Scenario',
            'scenario.txt',
            array(),
            array(),
            array()
        );
        $this->_object->run($scenario);
    }
}
