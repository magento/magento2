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
 * @package     Mage_Backend
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class Mage_Backend_Controller_Router_Default
 */
class Mage_Backend_Controller_Router_Validator_DefaultTest extends PHPUnit_Framework_TestCase
{
    /**
     * @magentoConfigFixture global/areas/adminhtml/frontName 0
     * @expectedException InvalidArgumentException
     * @magentoAppIsolation enabled
     */
    public function testConstructWithEmptyAreaFrontName()
    {
        $options = array(
            'area' => Mage::helper('Mage_Backend_Helper_Data')->getAreaCode(),
            'base_controller' => 'Mage_Backend_Controller_ActionAbstract',
            'frontName' => 'backend'
        );
        Mage::getModel('Mage_Backend_Controller_Router_Default', array('options' => $options));
    }

    /**
     * @magentoConfigFixture global/areas/adminhtml/frontName backend
     * @magentoAppIsolation enabled
     */
    public function testConstructWithNotEmptyAreaFrontName()
    {
        $options = array(
            'area' => Mage::helper('Mage_Backend_Helper_Data')->getAreaCode(),
            'base_controller' => 'Mage_Backend_Controller_ActionAbstract',
            'frontName' => 'backend'
        );
        Mage::getModel('Mage_Backend_Controller_Router_Default', array('options' => $options));
    }
}
