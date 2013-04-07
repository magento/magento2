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

class Mage_Webhook_Block_Adminhtml_Registration_Create_Form extends Mage_Backend_Block_Widget_Form
{
    const API_KEY_LENGTH = 32;
    const API_SECRET_LENGTH = 32;
    const MIN_TEXT_INPUT_LENGTH = 20;

    protected function _prepareForm()
    {
        $subscriber = Mage::registry('current_subscriber');
        /** @var $helper Mage_Webhook_Helper_Data */
        $helper = Mage::helper('Mage_Webhook_Helper_Data');
        $api_key = $helper->generateRandomString(self::API_KEY_LENGTH);
        $api_secret = $helper->generateRandomString(self::API_SECRET_LENGTH);
        $input_length = max(self::API_KEY_LENGTH, self::API_SECRET_LENGTH, self::MIN_TEXT_INPUT_LENGTH);

        /** @var $form Varien_Data_Form */
        $form = new Varien_Data_Form(array(
                'id' => 'api_user',
                'action' => $this->getUrl('*/*/register', array('id' => $subscriber->getId())),
                'method' => 'post',
            )
        );

//        $fieldset = $form->addFieldset('api_user_fieldset', array('legend' => $this->__('Create API User')));
        $fieldset = $form;

        $fieldset->addField('company', 'text', array(
            'label'     => $this->__('Company'),
            'name'      => 'company',
            'size'      => $input_length,
        ));

        $fieldset->addField('email', 'text', array(
            'label'     => $this->__('Contact Email'),
            'name'      => 'email',
            'class'     => 'email',
            'required'  => true,
            'size'      => $input_length,
        ));

        $fieldset->addField('apikey', 'text', array(
            'label'     => $this->__('API Key'),
            'name'      => 'apikey',
            'value'     => $api_key,
            'class'     => 'monospace',
            'required'  => true,
            'size'      => $input_length,
        ));

        $fieldset->addField('apisecret', 'text', array(
            'label'     => $this->__('API Secret'),
            'name'      => 'apisecret',
            'value'     => $api_secret,
            'class'     => 'monospace',
            'required'  => true,
            'size'      => $input_length,
        ));

        $form->setUseContainer(true);

        $this->setForm($form);

        return parent::_prepareForm();
    }
}