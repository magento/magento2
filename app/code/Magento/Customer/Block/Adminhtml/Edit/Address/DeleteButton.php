<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Address;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Customer\Ui\Component\Listing\Address\Column\Actions;

/**
 * Delete button on edit customer address form
 */
class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Get delete button data.
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getAddressId()) {
            $data = [
                'label' => __('Delete'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __(
                    'Are you sure you want to delete this address?'
                ) . '\', \'' . $this->getDeleteUrl() . '\')',
                'sort_order' => 15,
            ];
        }
        return $data;
    }

    /**
     * Get delete button url.
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDeleteUrl(): string
    {
        return $this->getUrl(
            Actions::CUSTOMER_ADDRESS_PATH_DELETE,
            ['parent_id' => $this->getCustomerId(), 'id' => $this->getAddressId()]
        );
    }
}
