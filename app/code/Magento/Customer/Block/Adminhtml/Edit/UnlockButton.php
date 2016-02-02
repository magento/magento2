<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Customer;

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
     * @var \Magento\Customer\Model\Customer
     */
    protected $customerModel;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\CustomerRegistry $customerRegistry
     * @param \Magento\Customer\Model\Customer $customerModel
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        CustomerRegistry $customerRegistry,
        Customer $customerModel
    ) {
        parent::__construct($context, $registry);
        $this->customerRegistry = $customerRegistry;
        $this->customerModel = $customerModel;
    }

    /**
     * Returns Unlock button data
     *
     * @return array
     */
    public function getButtonData()
    {
        $customer = $this->customerRegistry->retrieve($this->getCustomerId());
        $data = [];
        if ($this->customerModel->isCustomerLocked($customer->getLockExpires())) {
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
     * Returns customer unlock action URL
     *
     * @return string
     */
    protected function getUnlockUrl()
    {
        return $this->getUrl('customer/locks/unlock', ['customer_id' => $this->getCustomerId()]);
    }
}
