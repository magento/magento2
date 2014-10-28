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
        $this->_helper = new \Magento\TestFramework\Helper\Memory(
            new \Magento\Framework\Shell(new \Magento\Framework\Shell\CommandRenderer())
        );
    }

    /**
     * Test that application reinitialization produces no memory leaks
     */
    public function testAppReinitializationNoMemoryLeak()
    {
        if (extension_loaded('xdebug')) {
            $this->markTestSkipped('Xdebug extension may significantly affect memory consumption of a process.');
        }
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
        return \Magento\TestFramework\Helper\Memory::convertToBytes('1M');
    }
}
