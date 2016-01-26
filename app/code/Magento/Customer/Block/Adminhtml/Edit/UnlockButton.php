<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Helper\AccountManagement as AccountManagementHelper;

/**
 * Class UnlockButton
 */
class UnlockButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @var \Magento\Customer\Model\CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * AccountManagement Helper
     *
     * @var AccountManagementHelper
     */
    protected $accountManagementHelper;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\CustomerRegistry $customerRegistry
     * @param AccountManagementHelper $accountManagementHelper
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        CustomerRegistry $customerRegistry,
        AccountManagementHelper $accountManagementHelper
    ) {
        $this->customerRegistry = $customerRegistry;
        $this->accountManagementHelper = $accountManagementHelper;
        parent::__construct($context, $registry);
    }
    /**
     * @return array
     */
    public function getButtonData()
    {
        $customer = $this->customerRegistry->retrieve($this->getCustomerId());
        $data = [];
        if ($this->accountManagementHelper->isCustomerLocked($customer->getLockExpires())) {
            $data = [
                'label' => __('Unlock'),
                'class' => 'unlock unlock-customer',
                'on_click' => sprintf("location.href = '%s';", $this->getUnlockUrl()),
                'sort_order' => 50,
            ];
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getUnlockUrl()
    {
        return $this->getUrl('customer/locks/unlock', ['customer_id' => $this->getCustomerId()]);
    }
}
