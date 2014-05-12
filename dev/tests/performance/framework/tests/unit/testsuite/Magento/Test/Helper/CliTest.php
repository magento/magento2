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
namespace Magento\Test\Helper;

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
    const TEST_OPTION_NAME = 'name';

    const TEST_OPTION_VALUE = 'test_option_value';

    /**
     * Set up before test
     */
    public function setUp()
    {

        $this->_getOpt = $this->getMock('Zend_Console_Getopt', array('getOption'), array(array()));
        $this->_getOpt->expects(
            $this->any()
        )->method(
            'getOption'
        )->will(
            $this->returnValueMap(array(array(self::TEST_OPTION_NAME, self::TEST_OPTION_VALUE), array('xxx', null)))
        );

        \Magento\TestFramework\Helper\Cli::setOpt($this->_getOpt);
    }

    /**
     * Tesr down after test
     */
    public function tearDown()
    {
        $this->_getOpt = null;
        $this->_object = null;
    }

    /**
     * Test CLI helper
     */
    public function testCli()
    {
        $this->assertEquals(
            self::TEST_OPTION_VALUE,
            \Magento\TestFramework\Helper\Cli::getOption(self::TEST_OPTION_NAME)
        );
        $this->assertEquals(null, \Magento\TestFramework\Helper\Cli::getOption('xxx'));
        $this->assertEquals('default', \Magento\TestFramework\Helper\Cli::getOption('xxx', 'default'));
    }
}
