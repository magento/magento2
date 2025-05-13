<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Block\Adminhtml\Group;

use Magento\Customer\Block\Adminhtml\Edit\GenericButton;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class to get button details of AddCustomerGroup button
 */
class AddCustomerGroupButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Get button data for AddCustomerGroup button
     *
     * @return array
     */
    public function getButtonData(): array
    {
        return [
            'label' => __('Add New Customer Group'),
            'class' => 'primary',
            'url' => $this->getUrl('*/*/new'),
            'sort_order' => 80,
        ];
    }
}
