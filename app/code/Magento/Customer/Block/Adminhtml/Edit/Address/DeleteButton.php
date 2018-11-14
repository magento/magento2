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
        $confirm = __('Are you sure you want to delete this address?');
        if ($this->getAddressId()) {
            $data = [
                'label' => __('Delete'),
                'on_click' => '',
                'data_attribute' => [
                    'mage-init' => [
                        'Magento_Ui/js/form/button-adapter' => [
                            'actions' => [
                                [
                                    'targetName' => 'customer_address_form.customer_address_form',
                                    'actionName' => 'delete',
                                    'params' => [
                                        $this->getDeleteUrl(),
                                    ],

                                ],
                                [
                                    'targetName' => 'customer_form.areas.address.address.customer_address_update_modal',
                                    'actionName' => 'closeModal'
                                ],
                                [
                                    'targetName' => 'customer_address_listing.customer_address_listing',
                                    'actionName' => 'reload'
                                ]
                            ],
                        ],
                    ],
                ],
                'sort_order' => 20
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
