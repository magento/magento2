<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
            ['printStats'],
            [],
            '',
            false
        );
        $this->_activationPolicy = $this->getMock('stdClass', ['register_shutdown_function']);
        $this->_object = new \Magento\TestFramework\Bootstrap\Memory(
            $this->_memoryLimit,
            [$this->_activationPolicy, 'register_shutdown_function']
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
            $this->identicalTo([$this->_object, 'displayStats'])
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
            $this->identicalTo([$this->_memoryLimit, 'validateUsage'])
        );
        $this->_object->activateLimitValidation();
    }
}
