<?php
/**
 * Creates registration form
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
class Mage_Webhook_Block_Adminhtml_Registration_Create_Form extends Mage_Backend_Block_Widget_Form
{
    /** Constants for API user details */
    const API_KEY_LENGTH = 32;
    const API_SECRET_LENGTH = 32;
    const MIN_TEXT_INPUT_LENGTH = 20;

    /** Registry key for getting subscription data */
    const REGISTRY_KEY_CURRENT_SUBSCRIPTION = 'current_subscription';

    /** Data key for getting subscription id out of subscription data */
    const DATA_SUBSCRIPTION_ID = 'subscription_id';

    /** @var Varien_Data_Form_Factory */
    private $_formFactory;

    /** @var Mage_Core_Helper_Data  */
    private $_coreHelper;

    /** @var Mage_Core_Model_Registry  */
    private $_registry;

    /**
     * @param Mage_Core_Helper_Data $coreHelper
     * @param Mage_Core_Model_Registry $registry
     * @param Mage_Backend_Block_Template_Context $context
     * @param Varien_Data_Form_Factory $formFactory
     * @param array $data
     */
    public function __construct(
        Mage_Core_Helper_Data $coreHelper,
        Mage_Core_Model_Registry $registry,
        Mage_Backend_Block_Template_Context $context,
        Varien_Data_Form_Factory $formFactory,
        array $data = array()
    ) {
        parent::__construct($context, $data);

        $this->_formFactory = $formFactory;
        $this->_coreHelper = $coreHelper;
        $this->_registry = $registry;
    }

    /**
     * Prepares registration form
     *
     * @return Mage_Backend_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $subscription = $this->_registry->registry(self::REGISTRY_KEY_CURRENT_SUBSCRIPTION);
        $apiKey = $this->_generateRandomString(self::API_KEY_LENGTH);
        $apiSecret = $this->_generateRandomString(self::API_SECRET_LENGTH);
        $inputLength = max(self::API_KEY_LENGTH, self::API_SECRET_LENGTH, self::MIN_TEXT_INPUT_LENGTH);

        $form = $this->_formFactory->create(array(
                'id' => 'api_user',
                'action' => $this->getUrl('*/*/register', array('id' => $subscription[self::DATA_SUBSCRIPTION_ID])),
                'method' => 'post',
            )
        );

        $fieldset = $form;

        $fieldset->addField('company', 'text', array(
            'label'     => $this->__('Company'),
            'name'      => 'company',
            'size'      => $inputLength,
        ));

        $fieldset->addField('email', 'text', array(
            'label'     => $this->__('Contact Email'),
            'name'      => 'email',
            'class'     => 'email',
            'required'  => true,
            'size'      => $inputLength,
        ));

        $fieldset->addField('apikey', 'text', array(
            'label'     => $this->__('API Key'),
            'name'      => 'apikey',
            'value'     => $apiKey,
            'class'     => 'monospace',
            'required'  => true,
            'size'      => $inputLength,
        ));

        $fieldset->addField('apisecret', 'text', array(
            'label'     => $this->__('API Secret'),
            'name'      => 'apisecret',
            'value'     => $apiSecret,
            'class'     => 'monospace',
            'required'  => true,
            'size'      => $inputLength,
        ));

        $form->setUseContainer(true);

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Generates a random alphanumeric string
     *
     * @param int $length
     * @return string
     */
    private function _generateRandomString($length)
    {
        return $this->_coreHelper
            ->getRandomString($length, Mage_Core_Helper_Data::CHARS_DIGITS . Mage_Core_Helper_Data::CHARS_LOWERS);
    }
}