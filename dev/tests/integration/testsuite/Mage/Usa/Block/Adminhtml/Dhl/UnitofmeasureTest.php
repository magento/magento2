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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Usa_Block_Adminhtml_Dhl_UnitofmeasureTest extends Mage_Backend_Area_TestCase
{
    /**
     * @magentoAppIsolation enabled
     */
    public function testToHtml()
    {
        Mage::getObjectManager()->configure(array(
            'Mage_Core_Model_Layout' => array(
                'parameters' => array('area' => 'adminhtml')
            )
        ));
        /** @var $layout Mage_Core_Model_Layout */
        $layout = Mage::getSingleton('Mage_Core_Model_Layout');
        /** @var $block Mage_Usa_Block_Adminhtml_Dhl_Unitofmeasure */
        $block = $layout->createBlock('Mage_Usa_Block_Adminhtml_Dhl_Unitofmeasure');
        $this->assertNotEmpty($block->toHtml());
    }
}
