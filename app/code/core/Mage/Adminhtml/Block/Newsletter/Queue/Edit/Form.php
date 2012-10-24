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
 * Adminhtml newsletter queue edit form
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Adminhtml_Block_Newsletter_Queue_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare form for newsletter queue editing.
     * Form can be run from newsletter template grid by option "Queue newsletter"
     * or from  newsletter queue grid by edit option.
     *
     * @param void
     * @return Mage_Adminhtml_Block_Newsletter_Queue_Edit_Form
     */
    protected function _prepareForm()
    {
        /* @var $queue Mage_Newsletter_Model_Queue */
        $queue = Mage::getSingleton('Mage_Newsletter_Model_Queue');

        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'    =>  Mage::helper('Mage_Newsletter_Helper_Data')->__('Queue Information'),
            'class'    =>  'fieldset-wide'
        ));

        $dateFormat = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
        $timeFormat = Mage::app()->getLocale()->getTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);

        if($queue->getQueueStatus() == Mage_Newsletter_Model_Queue::STATUS_NEVER) {
            $fieldset->addField('date', 'date',array(
                'name'      =>    'start_at',
                'date_format' => $dateFormat,
                'time_format' => $timeFormat,
                'label'     =>    Mage::helper('Mage_Newsletter_Helper_Data')->__('Queue Date Start'),
                'image'     =>    $this->getSkinUrl('images/grid-cal.gif')
            ));

            if (!Mage::app()->hasSingleStore()) {
                $fieldset->addField('stores','multiselect',array(
                    'name'          => 'stores[]',
                    'label'         => Mage::helper('Mage_Newsletter_Helper_Data')->__('Subscribers From'),
                    'image'         => $this->getSkinUrl('images/grid-cal.gif'),
                    'values'        => Mage::getSingleton('Mage_Core_Model_System_Store')->getStoreValuesForForm(),
                    'value'         => $queue->getStores()
                ));
            }
            else {
                $fieldset->addField('stores', 'hidden', array(
                    'name'      => 'stores[]',
                    'value'     => Mage::app()->getStore(true)->getId()
                ));
            }
        } else {
            $fieldset->addField('date','date',array(
                'name'      => 'start_at',
                'disabled'  => 'true',
                'style'     => 'width:38%;',
                'date_format' => $dateFormat,
                'time_format' => $timeFormat,
                'label'     => Mage::helper('Mage_Newsletter_Helper_Data')->__('Queue Date Start'),
                'image'     => $this->getSkinUrl('images/grid-cal.gif')
            ));

            if (!Mage::app()->hasSingleStore()) {
                $fieldset->addField('stores','multiselect',array(
                    'name'          => 'stores[]',
                    'label'         => Mage::helper('Mage_Newsletter_Helper_Data')->__('Subscribers From'),
                    'image'         => $this->getSkinUrl('images/grid-cal.gif'),
                    'required'      => true,
                    'values'        => Mage::getSingleton('Mage_Core_Model_System_Store')->getStoreValuesForForm(),
                    'value'         => $queue->getStores()
                ));
            }
            else {
                $fieldset->addField('stores', 'hidden', array(
                    'name'      => 'stores[]',
                    'value'     => Mage::app()->getStore(true)->getId()
                ));
            }
        }

        if ($queue->getQueueStartAt()) {
            $form->getElement('date')->setValue(
                Mage::app()->getLocale()->date($queue->getQueueStartAt(), Varien_Date::DATETIME_INTERNAL_FORMAT)
            );
        }

        $fieldset->addField('subject', 'text', array(
            'name'      =>'subject',
            'label'     => Mage::helper('Mage_Newsletter_Helper_Data')->__('Subject'),
            'required'  => true,
            'value'     => (
                $queue->isNew() ? $queue->getTemplate()->getTemplateSubject() : $queue->getNewsletterSubject()
            )
        ));

        $fieldset->addField('sender_name', 'text', array(
            'name'      =>'sender_name',
            'label'     => Mage::helper('Mage_Newsletter_Helper_Data')->__('Sender Name'),
            'title'     => Mage::helper('Mage_Newsletter_Helper_Data')->__('Sender Name'),
            'required'  => true,
            'value'     => (
                $queue->isNew() ? $queue->getTemplate()->getTemplateSenderName() : $queue->getNewsletterSenderName()
            )
        ));

        $fieldset->addField('sender_email', 'text', array(
            'name'      =>'sender_email',
            'label'     => Mage::helper('Mage_Newsletter_Helper_Data')->__('Sender Email'),
            'title'     => Mage::helper('Mage_Newsletter_Helper_Data')->__('Sender Email'),
            'class'     => 'validate-email',
            'required'  => true,
            'value'     => (
                $queue->isNew() ? $queue->getTemplate()->getTemplateSenderEmail() : $queue->getNewsletterSenderEmail()
            )
        ));

        $widgetFilters = array('is_email_compatible' => 1);
        $wysiwygConfig = Mage::getSingleton('Mage_Cms_Model_Wysiwyg_Config')
            ->getConfig(array('widget_filters' => $widgetFilters));

        if ($queue->isNew()) {
            $fieldset->addField('text','editor', array(
                'name'      => 'text',
                'label'     => Mage::helper('Mage_Newsletter_Helper_Data')->__('Message'),
                'state'     => 'html',
                'required'  => true,
                'value'     => $queue->getTemplate()->getTemplateText(),
                'style'     => 'height: 600px;',
                'config'    => $wysiwygConfig
            ));

            $fieldset->addField('styles', 'textarea', array(
                'name'          =>'styles',
                'label'         => Mage::helper('Mage_Newsletter_Helper_Data')->__('Newsletter Styles'),
                'container_id'  => 'field_newsletter_styles',
                'value'         => $queue->getTemplate()->getTemplateStyles()
            ));
        } elseif (Mage_Newsletter_Model_Queue::STATUS_NEVER != $queue->getQueueStatus()) {
            $fieldset->addField('text','textarea', array(
                'name'      =>    'text',
                'label'     =>    Mage::helper('Mage_Newsletter_Helper_Data')->__('Message'),
                'value'     =>    $queue->getNewsletterText(),
            ));

            $fieldset->addField('styles', 'textarea', array(
                'name'          =>'styles',
                'label'         => Mage::helper('Mage_Newsletter_Helper_Data')->__('Newsletter Styles'),
                'value'         => $queue->getNewsletterStyles()
            ));

            $form->getElement('text')->setDisabled('true')->setRequired(false);
            $form->getElement('styles')->setDisabled('true')->setRequired(false);
            $form->getElement('subject')->setDisabled('true')->setRequired(false);
            $form->getElement('sender_name')->setDisabled('true')->setRequired(false);
            $form->getElement('sender_email')->setDisabled('true')->setRequired(false);
            $form->getElement('stores')->setDisabled('true');
        } else {
            $fieldset->addField('text','editor', array(
                'name'      =>    'text',
                'label'     =>    Mage::helper('Mage_Newsletter_Helper_Data')->__('Message'),
                'state'     => 'html',
                'required'  => true,
                'value'     =>    $queue->getNewsletterText(),
                'style'     => 'height: 600px;',
                'config'    => $wysiwygConfig
            ));

            $fieldset->addField('styles', 'textarea', array(
                'name'          =>'styles',
                'label'         => Mage::helper('Mage_Newsletter_Helper_Data')->__('Newsletter Styles'),
                'value'         => $queue->getNewsletterStyles(),
                'style'         => 'height: 300px;',
            ));
        }

        $this->setForm($form);
        return $this;
    }
}
