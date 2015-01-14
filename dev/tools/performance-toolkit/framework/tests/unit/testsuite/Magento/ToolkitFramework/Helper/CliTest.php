<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ToolkitFramework\Helper;

/**
 * Class CliTest
 *
 */
class CliTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Getopt object
     *
     * @var \Zend_Console_Getopt
     */
    protected $_getOpt;

    /**
     * Param constants
     */
    const TEST_OPTION_NAME  = 'name';
    const TEST_OPTION_VALUE = 'test_option_value';

    /**
     * Set up before test
     *
     * @return void
     */
    public function setUp()
    {
        $this->_getOpt =  $this->getMock(
            'Zend_Console_Getopt',
            ['getOption'],
            [[]]
        );
        $this->_getOpt->expects($this->any())->method('getOption')->will(
            $this->returnValueMap(
                [
                    [self::TEST_OPTION_NAME, self::TEST_OPTION_VALUE],
                    ['xxx', null],
                ]
            )
        );

        \Magento\ToolkitFramework\Helper\Cli::setOpt($this->_getOpt);
    }

    /**
     * Tear down after test
     *
     * @return void
     */
    public function tearDown()
    {
        $this->_getOpt = null;
        $this->_object = null;
    }

    /**
     * Test CLI helper
     *
     * @return void
     */
    public function testCli()
    {
        $this->assertEquals(
            self::TEST_OPTION_VALUE,
            \Magento\ToolkitFramework\Helper\Cli::getOption(self::TEST_OPTION_NAME)
        );
        $this->assertEquals(
            null,
            \Magento\ToolkitFramework\Helper\Cli::getOption('xxx')
        );
        $this->assertEquals(
            'default',
            \Magento\ToolkitFramework\Helper\Cli::getOption('xxx', 'default')
        );
    }
}
