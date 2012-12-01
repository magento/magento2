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
 * @package     Mage_Downloadable
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Downloadable_Block_Adminhtml_Catalog_Product_Edit_Tab_Downloadable_SamplesTest
    extends Mage_Downloadable_Block_Adminhtml_Catalog_Product_Edit_Tab_Downloadable_TestCaseAbstract
{
    protected function setUp()
    {
        parent::setUp();

        $objectManagerHelper = new Magento_Test_Helper_ObjectManager($this);
        $this->_block = $objectManagerHelper->getBlock(
            'Mage_Downloadable_Block_Adminhtml_Catalog_Product_Edit_Tab_Downloadable_Samples',
            array(
                'urlBuilder' => $this->getMock('Mage_Backend_Model_Url', array(), array(), '', false)
            )
        );
    }

    /**
     * Test that getConfig method retrieve Varien_Object object
     */
    public function testGetConfig()
    {
        // we have to set strict error reporting mode and enable mage developer mode to convert notice to exception
        error_reporting(E_ALL | E_STRICT);
        ini_set('display_errors', 1);
        Mage::setIsDeveloperMode(true);

        $this->assertInstanceOf('Varien_Object', $this->_block->getConfig());
    }
}
