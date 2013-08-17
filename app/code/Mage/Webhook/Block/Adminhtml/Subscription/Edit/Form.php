<?php
/**
 * Form to edit webhook subscription
 *
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
class Mage_Webhook_Block_Adminhtml_Subscription_Edit_Form extends Mage_Backend_Block_Widget_Form
{
    /** Key used to store subscription data into the registry */
    const REGISTRY_KEY_CURRENT_SUBSCRIPTION = 'current_subscription';

    /** Keys used to retrieve values from subscription data array */
    const DATA_SUBSCRIPTION_ID = 'subscription_id';
    /** @var Varien_Data_Form_Factory $_formFactory */
    private $_formFactory;

    /** @var  Mage_Core_Model_Registry $_registry */
    private $_registry;

    /** @var  Mage_Webhook_Model_Source_Format $_format */
    private $_format;

    /** @var  Mage_Webhook_Model_Source_Authentication $_authentication */
    private $_authentication;

    /** @var  Mage_Webhook_Model_Source_Hook  $_hook */
    private $_hook;

    /**
     * @param Varien_Data_Form_Factory $formFactory
     * @param Mage_Core_Model_Registry $registry
     * @param Mage_Backend_Block_Template_Context $context
     * @param Mage_Webhook_Model_Source_Format $format
     * @param Mage_Webhook_Model_Source_Authentication $authentication
     * @param Mage_Webhook_Model_Source_Hook $hook
     * @param array $data
     */
    public function __construct(
        Varien_Data_Form_Factory $formFactory,
        Mage_Core_Model_Registry $registry,
        Mage_Backend_Block_Template_Context $context,
        Mage_Webhook_Model_Source_Format $format,
        Mage_Webhook_Model_Source_Authentication $authentication,
        Mage_Webhook_Model_Source_Hook $hook,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->_formFactory = $formFactory;
        $this->_registry = $registry;
        $this->_format = $format;
        $this->_authentication = $authentication;
        $this->_hook = $hook;
    }


    /**
     * Prepares subscription editor form
     *
     * @return Mage_Backend_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $subscriptionData = $this->_registry->registry(self::REGISTRY_KEY_CURRENT_SUBSCRIPTION);

        $form = $this->_formFactory->create(
            array(
                 'id'     => 'edit_form',
                 'action' => $this->getUrl(
                     '*/*/save',
                     array('id' => $subscriptionData[self::DATA_SUBSCRIPTION_ID])
                 ),
                 'method' => 'post'
            )
        );

        $fieldset = $form->addFieldset('subscription_fieldset', array('legend' => $this->__('Subscription')));
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
            'format', 'select',
            array(
                 'name'   => 'format',
                 'label'  => $this->__('Format'),
                 'title'  => $this->__('Format'),
                 'values' => $this->_format->getFormatsForForm(),
            )
        );

        $fieldset->addField(
            'authentication_type', 'select',
            array(
                 'name'   => 'authentication_type',
                 'label'  => $this->__('Authentication Types'),
                 'title'  => $this->__('Authentication Types'),
                 'values' => $this->_authentication->getAuthenticationsForForm(),
            )
        );

        $versionData = array(
            'label' => $this->__('Version'),
            'name'  => 'version',
        );
        if (isset($subscriptionData['extension_id']) && $subscriptionData['extension_id']) {
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
                 'values'   => $this->_hook->getTopicsForForm(),
            )
        );

        $form->setUseContainer(true);
        $form->setValues($subscriptionData);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
