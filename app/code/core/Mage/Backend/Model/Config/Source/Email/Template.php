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
 * @package     Mage_Backend
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Config config system template source
 *
 * @category   Mage
 * @package    Mage_Backend
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Backend_Model_Config_Source_Email_Template extends Varien_Object
    implements Mage_Core_Model_Option_ArrayInterface
{
    /**
     * Generate list of email templates
     *
     * @return array
     */
    public function toOptionArray()
    {
        if(!$collection = Mage::registry('config_system_email_template')) {
            $collection = Mage::getResourceModel('Mage_Core_Model_Resource_Email_Template_Collection')
                ->load();

            Mage::register('config_system_email_template', $collection);
        }
        $options = $collection->toOptionArray();
        $templateName = Mage::helper('Mage_Backend_Helper_Data')->__('Default Template');
        $nodeName = str_replace('/', '_', $this->getPath());
        $templateLabelNode = Mage::app()->getConfig()->getNode(
            Mage_Core_Model_Email_Template::XML_PATH_TEMPLATE_EMAIL . '/' . $nodeName . '/label'
        );
        if ($templateLabelNode) {
            $module = (string)$templateLabelNode->getParent()->getAttribute('module');
            $templateName = Mage::helper($module)->__((string)$templateLabelNode);
            $templateName = Mage::helper('Mage_Backend_Helper_Data')->__('%s (Default)', $templateName);
        }
        array_unshift(
            $options,
            array(
                'value'=> $nodeName,
                'label' => $templateName
            )
        );
        return $options;
    }

}
