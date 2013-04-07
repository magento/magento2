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
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Second part of Mage_Core_Model_Config testing:
 * - Mage factory behaviour is tested
 *
 * @see Mage_Core_Model_ConfigTest
 */
class Mage_Core_Model_ConfigFactoryTest extends PHPUnit_Framework_TestCase
{
    /** @var Mage_Core_Model_Config */
    protected $_model;

    public function setUp()
    {
        $this->_model = Mage::getModel('Mage_Core_Model_Config');
    }

    public function testGetModelInstance()
    {
        $this->assertInstanceOf('Mage_Core_Model_Config', $this->_model->getModelInstance('Mage_Core_Model_Config'));
    }

    public function testGetResourceModelInstance()
    {
        $this->assertInstanceOf(
            'Mage_Core_Model_Resource_Config',
            $this->_model->getResourceModelInstance('Mage_Core_Model_Resource_Config')
        );
    }
}
