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
 * @package     Mage_DesignEditor
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_DesignEditor_Model_ObserverTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_DesignEditor_Model_Observer
     */
    protected $_model;

    protected function tearDown()
    {
        unset($this->_model);
    }

    /**
     * @param int $themeId
     * @dataProvider setThemeDataProvider
     */
    public function testSetTheme($themeId)
    {
        /** @var $session Mage_Backend_Model_Session */
        $session = $this->getMock('Mage_Backend_Model_Session', null, array(), '', false);
        $session->setData('theme_id', $themeId);

        $design = $this->getMock('Mage_Core_Model_Design_Package', array('setDesignTheme'), array(), '', false);
        if ($themeId !== null) {
            $design->expects($this->once())
                ->method('setDesignTheme')
                ->with($themeId);
        } else {
            $design->expects($this->never())
                ->method('setDesignTheme');
        }

        $this->_model = new Mage_DesignEditor_Model_Observer($session, $design);
        $this->_model->setTheme();
    }

    /**
     * @return array
     */
    public function setThemeDataProvider()
    {
        return array(
            'no theme id'      => array('$themeId' => null),
            'correct theme id' => array('$themeId' => 1),
        );
    }
}
