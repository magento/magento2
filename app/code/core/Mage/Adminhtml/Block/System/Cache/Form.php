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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Cache management form page
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_System_Cache_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Initialize cache management form
     *
     * @return Mage_Adminhtml_Block_System_Cache_Form
     */
    public function initForm()
    {
        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('cache_enable', array(
            'legend' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Cache Control')
        ));

        $fieldset->addField('all_cache', 'select', array(
            'name'=>'all_cache',
            'label'=>'<strong>'.Mage::helper('Mage_Adminhtml_Helper_Data')->__('All Cache').'</strong>',
            'value'=>1,
            'options'=>array(
                '' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('No change'),
                'refresh' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Refresh'),
                'disable' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Disable'),
                'enable' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Enable'),
            ),
        ));

        foreach (Mage::helper('Mage_Core_Helper_Data')->getCacheTypes() as $type=>$label) {
            $fieldset->addField('enable_'.$type, 'checkbox', array(
                'name'=>'enable['.$type.']',
                'label'=>Mage::helper('Mage_Adminhtml_Helper_Data')->__($label),
                'value'=>1,
                'checked'=>(int)Mage::app()->useCache($type),
                //'options'=>$options,
            ));
        }

        $fieldset = $form->addFieldset('beta_cache_enable', array(
            'legend' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Cache Control (beta)')
        ));

        foreach (Mage::helper('Mage_Core_Helper_Data')->getCacheBetaTypes() as $type=>$label) {
            $fieldset->addField('beta_enable_'.$type, 'checkbox', array(
                'name'=>'beta['.$type.']',
                'label'=>Mage::helper('Mage_Adminhtml_Helper_Data')->__($label),
                'value'=>1,
                'checked'=>(int)Mage::app()->useCache($type),
            ));
        }

        $this->setForm($form);

        return $this;
    }
}
