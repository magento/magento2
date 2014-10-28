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

/**
 * Test class for \Magento\TestFramework\Bootstrap\Memory.
 */
namespace Magento\Test\Bootstrap;

class MemoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Bootstrap\Memory
     */
    protected $_object;

    /**
     * @var \Magento\TestFramework\MemoryLimit|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_memoryLimit;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_activationPolicy;

    protected function setUp()
    {
        $this->_memoryLimit = $this->getMock(
            'Magento\TestFramework\MemoryLimit',
            array('printStats'),
            array(),
            '',
            false
        );
        $this->_activationPolicy = $this->getMock('stdClass', array('register_shutdown_function'));
        $this->_object = new \Magento\TestFramework\Bootstrap\Memory(
            $this->_memoryLimit,
            array($this->_activationPolicy, 'register_shutdown_function')
        );
    }

    protected function tearDown()
    {
        $this->_memoryLimit = null;
        $this->_activationPolicy = null;
        $this->_object = null;
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Activation policy is expected to be a callable.
     */
    public function testConstructorException()
    {
        new \Magento\TestFramework\Bootstrap\Memory($this->_memoryLimit, 'non_existing_callable');
    }

    public function testDisplayStats()
    {
        $eol = PHP_EOL;
        $this->expectOutputString("{$eol}=== Memory Usage System Stats ==={$eol}Dummy Statistics{$eol}");
        $this->_memoryLimit->expects(
            $this->once()
        )->method(
            'printStats'
        )->will(
            $this->returnValue('Dummy Statistics')
        );
        $this->_object->displayStats();
    }

    public function testActivateStatsDisplaying()
    {
        $this->_activationPolicy->expects(
            $this->once()
        )->method(
            'register_shutdown_function'
        )->with(
            $this->identicalTo(array($this->_object, 'displayStats'))
        );
        $this->_object->activateStatsDisplaying();
    }

    public function testActivateLimitValidation()
    {
        $this->_activationPolicy->expects(
            $this->once()
        )->method(
            'register_shutdown_function'
        )->with(
            $this->identicalTo(array($this->_memoryLimit, 'validateUsage'))
        );
        $this->_object->activateLimitValidation();
    }
}
