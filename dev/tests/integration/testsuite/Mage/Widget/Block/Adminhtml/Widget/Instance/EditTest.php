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
 * @package     Mage_Widget
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @magentoAppArea adminhtml
 */
class Mage_Widget_Block_Adminhtml_Widget_Instance_EditTest extends PHPUnit_Framework_TestCase
{
    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testConstruct()
    {
        $type = 'Mage_Catalog_Block_Product_Widget_New';
        $theme = Mage::getDesign()->setDefaultDesignTheme()->getDesignTheme();

        /** @var $widgetInstance Mage_Widget_Model_Widget_Instance */
        $widgetInstance = Mage::getModel('Mage_Widget_Model_Widget_Instance');
        $widgetInstance
            ->setType($type)
            ->setThemeId($theme->getId())
            ->save();
        Mage::register('current_widget_instance', $widgetInstance);

        Mage::app()->getRequest()->setParam('instance_id', $widgetInstance->getId());
        $block = Mage::app()->getLayout()->createBlock('Mage_Widget_Block_Adminhtml_Widget_Instance_Edit', 'widget');
        $this->assertArrayHasKey('widget-delete_button', $block->getLayout()->getAllBlocks());
    }
}
