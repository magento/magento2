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
 * Test class for Mage_Core_Model_Image_AdapterFactory
 * @magentoAppArea adminhtml
 */
class Mage_Core_Model_Image_AdapterFactoryTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->_model = Mage::getModel('Mage_Core_Model_Image_AdapterFactory');
        $this->_config = Mage::getModel('Mage_Core_Model_Store_Config');
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testCreate()
    {
        $result = $this->_model->create();
        $this->assertInstanceOf('Varien_Image_Adapter_Abstract', $result);
        $this->assertNotEmpty($this->_config->getConfig(Mage_Core_Model_Image_AdapterFactory::XML_PATH_IMAGE_ADAPTER));
    }
}
