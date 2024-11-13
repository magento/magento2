<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\TestFramework\Bootstrap\Memory.
 */
namespace Magento\Test\Bootstrap;

use Magento\TestFramework\MemoryLimit;

class MemoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\Bootstrap\Memory
     */
    protected $_object;

    /**
     * @var MemoryLimit|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_memoryLimit;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_activationPolicy;

    protected function setUp(): void
    {
        $this->_memoryLimit = $this->createPartialMock(MemoryLimit::class, ['printStats']);
        $this->_activationPolicy = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['register_shutdown_function'])
            ->getMock();
        $this->_object = new \Magento\TestFramework\Bootstrap\Memory(
            $this->_memoryLimit,
            [$this->_activationPolicy, 'register_shutdown_function']
        );
    }

    protected function tearDown(): void
    {
        $this->_memoryLimit = null;
        $this->_activationPolicy = null;
        $this->_object = null;
    }

    /**
     */
    public function testConstructorException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Activation policy is expected to be a callable.');

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
        )->willReturn(
            'Dummy Statistics'
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
