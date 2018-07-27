<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Tab;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

/**
 * Customer account form block
 */
class Newsletter extends \Magento\Backend\Block\Widget\Form\Generic implements TabInterface
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Customer::tab/newsletter.phtml';

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $_subscriberFactory;

    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param AccountManagementInterface $customerAccountManagement
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        AccountManagementInterface $customerAccountManagement,
        array $data = []
    ) {
        $this->_subscriberFactory = $subscriberFactory;
        $this->customerAccountManagement = $customerAccountManagement;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Return Tab label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Newsletter');
    }

    /**
     * Return Tab title
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Newsletter');
    }

    /**
     * Tab class getter
     *
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl()
    {
        return '';
    }

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Initialize the form.
     *
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function initForm()
    {
        if (!$this->canShowTab()) {
            return $this;
        }
        /**@var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('_newsletter');
        $customerId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        $subscriber = $this->_subscriberFactory->create()->loadByCustomerId($customerId);
        $this->_coreRegistry->register('subscriber', $subscriber);

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Newsletter Information')]);

        $fieldset->addField(
            'subscription',
            'checkbox',
            [
                'label' => __('Subscribed to Newsletter'),
                'name' => 'subscription',
                'data-form-part' => $this->getData('target_form'),
                'onchange' => 'this.value = this.checked;'
            ]
        );

        if ($this->customerAccountManagement->isReadonly($customerId)) {
            $form->getElement('subscription')->setReadonly(true, true);
        }
        $isSubscribed = $subscriber->isSubscribed();
        $form->setValues(['subscription' => $isSubscribed ? 'true' : 'false']);
        $form->getElement('subscription')->setIsChecked($isSubscribed);

        $this->updateFromSession($form, $customerId);

        $changedDate = $this->getStatusChangedDate();
        if ($changedDate) {
            $fieldset->addField(
                'change_status_date',
                'label',
                [
                    'label' => $isSubscribed ? __('Last Date Subscribed') : __('Last Date Unsubscribed'),
                    'value' => $changedDate,
                    'bold' => true
                ]
            );
        }

        $this->setForm($form);
        return $this;
    }

    /**
     * Update form elements from session data
     *
     * @param \Magento\Framework\Data\Form $form
     * @param int $customerId
     * @return void
     */
    protected function updateFromSession(\Magento\Framework\Data\Form $form, $customerId)
    {
        $data = $this->_backendSession->getCustomerFormData();
        if (!empty($data)) {
            $dataCustomerId = isset($data['customer']['entity_id']) ? $data['customer']['entity_id'] : null;
            if (isset($data['subscription']) && $dataCustomerId == $customerId) {
                $form->getElement('subscription')->setIsChecked($data['subscription']);
            }
        }
    }

    /**
     * Retrieve the date when the subscriber status changed.
     *
     * @return null|string
     */
    public function getStatusChangedDate()
    {
        $subscriber = $this->_coreRegistry->registry('subscriber');
        if ($subscriber->getChangeStatusAt()) {
            return $this->formatDate(
                $subscriber->getChangeStatusAt(),
                \IntlDateFormatter::MEDIUM,
                true
            );
        }

        return null;
    }

    /**
     * Prepare the layout.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'grid',
            $this->getLayout()->createBlock(
                'Magento\Customer\Block\Adminhtml\Edit\Tab\Newsletter\Grid',
                'newsletter.grid'
            )
        );
        parent::_prepareLayout();
        return $this;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->canShowTab()) {
            $this->initForm();
            return parent::_toHtml();
        } else {
            return '';
        }
    }
}
