<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class ResetPasswordButton
 */
class ResetPasswordButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $customerId = $this->getCustomerId();
        $data = [];
        if ($customerId) {
            $data = [
                'label' => __('Reset Password'),
                'class' => 'reset reset-password',
                'on_click' => sprintf("location.href = '%s';", $this->getResetPasswordUrl()),
                'sort_order' => 60,
            ];
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getResetPasswordUrl()
    {
        return $this->getUrl('customer/index/resetPassword', ['customer_id' => $this->getCustomerId()]);
    }
}
