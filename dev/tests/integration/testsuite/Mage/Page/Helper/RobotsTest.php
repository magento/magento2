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
 * @package     Mage_Page
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Page_Helper_Robots
 */
class Mage_Page_Helper_RobotsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Page_Helper_Robots
     */
    protected $_helper;

    protected function setUp()
    {
        $this->_helper = Mage::helper('Mage_Page_Helper_Robots');
    }

    /**
     * @covers Mage_Page_Helper_RobotsTest::getRobotsDefaultCustomInstructions
     */
    public function testGetRobotsDefaultCustomInstructions()
    {
        $this->assertStringEqualsFile(
            __DIR__ . '/../_files/robots.txt',
            $this->_helper->getRobotsDefaultCustomInstructions(),
            'robots.txt default custom instructions are invalid'
        );
    }
}
