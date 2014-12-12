<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Customer\Block\Adminhtml\Edit;

use Magento\Ui\Component\Control\ButtonProviderInterface;

/**
 * Class InvalidateTokenButton
 * @package Magento\Customer\Block\Adminhtml\Edit
 */
class InvalidateTokenButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $customerId = $this->getCustomerId();
        $data = [];
        if ($customerId) {
            $deleteConfirmMsg = __("Are you sure you want to revoke the customer\'s tokens?");
            $data = [
                'label' => __('Force Sign-In'),
                'class' => 'invalidate-token',
                'on_click' => 'deleteConfirm(\'' . $deleteConfirmMsg . '\', \'' . $this->getInvalidateTokenUrl() .
                    '\')',
                'sort_order' => 50,
            ];
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getInvalidateTokenUrl()
    {
        return $this->getUrl('customer/customer/invalidateToken', ['customer_id' => $this->getCustomerId()]);
    }
}
