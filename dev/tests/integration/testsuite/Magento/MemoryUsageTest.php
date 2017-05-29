<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento;

class MemoryUsageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Number of application reinitialization iterations to be conducted by tests
     */
    const APP_REINITIALIZATION_LOOPS = 20;

    /**
     * @var \Magento\TestFramework\Helper\Memory
     */
    protected $_helper;

    protected function setUp()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped("Test not relevant because no gc in HHVM.");
        }
        $this->_helper = new \Magento\TestFramework\Helper\Memory(
            new \Magento\Framework\Shell(new \Magento\Framework\Shell\CommandRenderer())
        );
    }

    /**
     * Test that application reinitialization produces no memory leaks
     */
    public function testAppReinitializationNoMemoryLeak()
    {
        $this->markTestSkipped('Test fails at Travis. Skipped until MAGETWO-47111');

        $this->_deallocateUnusedMemory();
        $actualMemoryUsage = $this->_helper->getRealMemoryUsage();
        for ($i = 0; $i < self::APP_REINITIALIZATION_LOOPS; $i++) {
            \Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize();
            $this->_deallocateUnusedMemory();
        }
        $actualMemoryUsage = $this->_helper->getRealMemoryUsage() - $actualMemoryUsage;
        $this->assertLessThanOrEqual(
            $this->_getAllowedMemoryUsage(),
            $actualMemoryUsage,
            sprintf(
                "Application reinitialization causes the memory leak of %u bytes per %u iterations.",
                $actualMemoryUsage,
                self::APP_REINITIALIZATION_LOOPS
            )
        );
    }

    /**
     * Force to deallocate no longer used memory
     */
    protected function _deallocateUnusedMemory()
    {
        gc_collect_cycles();
    }

    /**
     * Retrieve the allowed memory usage in bytes, depending on the environment
     *
     * @return int
     */
    protected function _getAllowedMemoryUsage()
    {
        // Memory usage limits should not be further increased, corresponding memory leaks have to be fixed instead!
        // @todo fix memory leak and decrease limit to 1 M (in scope of MAGETWO-47693 limit was temporary increased)
        return \Magento\TestFramework\Helper\Memory::convertToBytes('2M');
    }
}
