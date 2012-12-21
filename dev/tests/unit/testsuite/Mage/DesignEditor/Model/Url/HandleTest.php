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
 * @package     Mage_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_DesignEditor_Model_Url_HandleTest extends PHPUnit_Framework_TestCase
{
    /**#@+
     * Test route params
     */
    const FRONT_NAME = 'vde';
    const ROUTE_PATH = 'design';
    /**#@-*/

    /**
     * @var Mage_DesignEditor_Model_Url_Handle
     */
    protected $_model;

    /**
     * @var Mage_DesignEditor_Helper_Data
     */
    protected $_helper;

    /**
     * @var array
     */
    protected $_testData = array(1,'test');

    public function setUp()
    {
        $this->_helper = $this->getMock('Mage_DesignEditor_Helper_Data', array('getFrontName'), array(), '', false);
        $this->_model = new Mage_DesignEditor_Model_Url_Handle($this->_helper, $this->_testData);
    }

    public function testConstruct()
    {
        $this->assertAttributeEquals($this->_helper, '_helper', $this->_model);
        $this->assertAttributeEquals($this->_testData, '_data', $this->_model);
    }

    public function testGetRoutePath()
    {
        $this->_helper->expects($this->once())
            ->method('getFrontName')
            ->will($this->returnValue(self::FRONT_NAME));

        $this->_model->setData('route_path', self::ROUTE_PATH);
        $this->assertEquals(self::FRONT_NAME . '/' . self::ROUTE_PATH, $this->_model->getRoutePath());
    }
}
