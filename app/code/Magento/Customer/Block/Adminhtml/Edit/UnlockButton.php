<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Customer\Model\CustomerRegistry;

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
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\CustomerRegistry $customerRegistry
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        CustomerRegistry $customerRegistry
    ) {
        parent::__construct($context, $registry);
        $this->customerRegistry = $customerRegistry;
    }

    /**
     * Returns Unlock button data
     *
     * @return array
     */
    public function getButtonData()
    {
        $customerId = $this->getCustomerId();
        $data = [];
        if ($customerId) {
            $customer = $this->customerRegistry->retrieve($customerId);
            if ($customer->isCustomerLocked()) {
                $data = [
                    'label' => __('Unlock'),
                    'class' => 'unlock unlock-customer',
                    'on_click' => sprintf("location.href = '%s';", $this->getUnlockUrl()),
                    'sort_order' => 50,
                ];
            }
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
