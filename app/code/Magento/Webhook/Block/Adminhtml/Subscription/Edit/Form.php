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
 * @category    Magento
 * @package     Magento_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
namespace Magento\Webhook\Block\Adminhtml\Subscription\Edit;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /** Key used to store subscription data into the registry */
    const REGISTRY_KEY_CURRENT_SUBSCRIPTION = 'current_subscription';

    /** Keys used to retrieve values from subscription data array */
    const DATA_SUBSCRIPTION_ID = 'subscription_id';
    const DATA_ALIAS = 'alias';

    /** @var  \Magento\Webhook\Model\Source\Format $_format */
    protected $_format;

    /** @var  \Magento\Webhook\Model\Source\Authentication $_authentication */
    protected $_authentication;

    /** @var  \Magento\Webhook\Model\Source\Hook  $_hook */
    protected $_hook;

    /**
     * @param \Magento\Webhook\Model\Source\Format $format
     * @param \Magento\Webhook\Model\Source\Authentication $authentication
     * @param \Magento\Webhook\Model\Source\Hook $hook
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Data\Form\Factory $formFactory
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Webhook\Model\Source\Format $format,
        \Magento\Webhook\Model\Source\Authentication $authentication,
        \Magento\Webhook\Model\Source\Hook $hook,
        \Magento\Core\Model\Registry $registry,
        \Magento\Data\Form\Factory $formFactory,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Backend\Block\Template\Context $context,
        array $data = array()
    ) {
        parent::__construct($registry, $formFactory, $coreData, $context, $data);
        $this->_formFactory = $formFactory;
        $this->_registry = $registry;
        $this->_format = $format;
        $this->_authentication = $authentication;
        $this->_hook = $hook;
    }

    /**
     * Prepares subscription editor form
     *
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
        $subscriptionData = $this->_registry->registry(self::REGISTRY_KEY_CURRENT_SUBSCRIPTION);

        $subscriptionId = isset($subscriptionData[self::DATA_SUBSCRIPTION_ID])
            ? $subscriptionData[self::DATA_SUBSCRIPTION_ID]
            : 0;
        $form = $this->_formFactory->create(array(
            'attributes' => array(
                 'id'     => 'edit_form',
                 'action' => $this->getUrl('*/*/save', array('id' => $subscriptionId)),
                 'method' => 'post',
            ))
        );

        // We don't want to allow subscriptions defined in config to be edited by the user.
        $disabled = isset($subscriptionData[self::DATA_ALIAS]) && !empty($subscriptionData[self::DATA_ALIAS]);

        $fieldset = $form->addFieldset('subscription_fieldset', array('legend' => __('Subscription')));

        $fieldset->addField(
            'name', 'text',
            array(
                'label'     => __('Name'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'name',
                'disabled'  => $disabled,
            )
        );

        $fieldset->addField(
            'endpoint_url', 'text',
            array(
                'label'     => __('Endpoint URL'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'endpoint_url',
                'disabled'  => $disabled,
            )
        );

        $fieldset->addField(
            'format', 'select',
            array(
                'name'      => 'format',
                'label'     => __('Format'),
                'title'     => __('Format'),
                'values'    => $this->_format->getFormatsForForm(),
                'disabled'  => $disabled,
            )
        );

        $fieldset->addField(
            'authentication_type', 'select',
            array(
                'name'      => 'authentication_type',
                'label'     => __('Authentication Types'),
                'title'     => __('Authentication Types'),
                'values'    => $this->_authentication->getAuthenticationsForForm(),
                'disabled'  => $disabled,
            )
        );

        $fieldset->addField(
            'topics', 'multiselect',
            array(
                'name'      => 'topics[]',
                'label'     => __('Topics'),
                'title'     => __('Topics'),
                'required'  => true,
                'values'    => $this->_hook->getTopicsForForm(),
                'disabled'  => $disabled,
            )
        );

        $form->setUseContainer(true);
        $form->setValues($subscriptionData);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
