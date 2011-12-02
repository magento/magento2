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
 * @category    Mage
 * @package     Mage_Widget
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Widget Observer model
 *
 * @category   Mage
 * @package    Mage_Widget
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Widget_Model_Observer
{
    /**
     * Add additional settings to wysiwyg config for Widgets Insertion Plugin
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Widget_Model_Observer
     */
    public function prepareWidgetsPluginConfig(Varien_Event_Observer $observer)
    {
        $config = $observer->getEvent()->getConfig();

        if ($config->getData('add_widgets')) {
            $settings = Mage::getModel('Mage_Widget_Model_Widget_Config')->getPluginSettings($config);
            $config->addData($settings);
        }
        return $this;
    }

}
