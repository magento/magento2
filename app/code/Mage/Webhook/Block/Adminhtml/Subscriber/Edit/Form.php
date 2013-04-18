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
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webhook_Block_Adminhtml_Subscriber_Edit_Form extends Mage_Backend_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $subscriber = Mage::registry('current_subscriber');
        $form       = new Varien_Data_Form(
            array(
                 'id'     => 'edit_form',
                 'action' => $this->getUrl(
                     '*/*/save',
                     array('id' => $subscriber->getId())
                 ),
                 'method' => 'post'
            )
        );

        $fieldset = $form->addFieldset('subscriber_fieldset', array('legend' => $this->__('Subscriber')));
        $fieldset->addField(
            'name', 'text',
            array(
                 'label'    => $this->__('Name'),
                 'class'    => 'required-entry',
                 'required' => true,
                 'name'     => 'name',
            )
        );

        $fieldset->addField(
            'endpoint_url', 'text',
            array(
                 'label'    => $this->__('Endpoint URL'),
                 'class'    => 'required-entry',
                 'required' => true,
                 'name'     => 'endpoint_url',
            )
        );

        $fieldset->addField(
            'mapping', 'select',
            array(
                 'name'   => 'mapping',
                 'label'  => $this->__('Mapping'),
                 'title'  => $this->__('Mapping'),
                 'values' => Mage::getSingleton('Mage_Webhook_Model_Source_Mapping')
                         ->getMappingsForForm(),
            )
        );

        $fieldset->addField(
            'format', 'select',
            array(
                 'name'   => 'format',
                 'label'  => $this->__('Format'),
                 'title'  => $this->__('Format'),
                 'values' => Mage::getSingleton('Mage_Webhook_Model_Source_Format')
                         ->getFormatsForForm(),
            )
        );

        $fieldset->addField(
            'authentication_type', 'select',
            array(
                 'name'   => 'authentication_type',
                 'label'  => $this->__('Authentication Types'),
                 'title'  => $this->__('Authentication Types'),
                 'values' => Mage::getSingleton('Mage_Webhook_Model_Source_Authentication')
                         ->getAuthenticationsForForm(),
            )
        );

        $versionData = array(
            'label' => $this->__('Version'),
            'name'  => 'version',
        );
        if ($subscriber->getExtensionId()) {
            $versionData['readonly'] = 'readonly';
            $versionData['class']    = 'disabled';
        }
        $fieldset->addField('version', 'text', $versionData);

        $fieldset->addField(
            'topics', 'multiselect',
            array(
                 'name'     => 'topics[]',
                 'label'    => $this->__('Topics'),
                 'title'    => $this->__('Topics'),
                 'required' => true,
                 'values'   => Mage::getSingleton('Mage_Webhook_Model_Source_Hook')
                         ->getTopicsForForm(),
            )
        );

        $values = $subscriber->getData();
        if ($subscriber && $subscriber->getId()) {
            $values['topics'] = $subscriber->getTopics();
        }

        $form->setUseContainer(true);
        $form->setValues($values);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
