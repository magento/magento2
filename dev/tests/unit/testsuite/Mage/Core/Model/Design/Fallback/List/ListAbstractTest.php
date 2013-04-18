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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_Design_Fallback_List_ListAbstractTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Design_Fallback_List_ListAbstract
     */
    protected $_model;

    /**
     * @var Mage_Core_Model_Dir
     */
    protected $_dirs;

    public function setUp()
    {
        $this->_dirs = $this->getMock('Mage_Core_Model_Dir', array(), array(), '', false);
        $this->_model = $this->getMockForAbstractClass('Mage_Core_Model_Design_Fallback_List_ListAbstract',
            array($this->_dirs));
    }

    public function testConstructor()
    {
        $this->_model->expects($this->once())->method('_getFallbackRules');
        $this->_model->__construct($this->_dirs);
    }

    public function testGetPatternDirs()
    {
        $ruleOne = $this->getMock('Mage_Core_Model_Design_Fallback_Rule_Simple', array('getPatternDirs'), array(), '',
            false);
        $ruleOne->expects($this->once())
            ->method('getPatternDirs')
            ->will($this->returnValue(array(1)));

        $ruleTwo = $this->getMock('Mage_Core_Model_Design_Fallback_Rule_Simple', array('getPatternDirs'), array(), '',
            false);
        $ruleTwo->expects($this->once())
            ->method('getPatternDirs')
            ->will($this->returnValue(array(2)));

        $rules = new ReflectionProperty($this->_model, '_rules');
        $rules->setAccessible(true);
        $rules->setValue($this->_model, array($ruleOne, $ruleTwo));

        $this->assertSame(array(1,2), $this->_model->getPatternDirs(array()));
    }
}
