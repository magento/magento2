<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflineShipping\Model\Observer\SalesRule;

use Magento\OfflineShipping\Model\SalesRule\Rule;

/**
 * Checkout cart shipping block plugin
 *
 * @author    Magento Core Team <core@magentocommerce.com>
 */
class ActionsTab
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function prepareForm($observer)
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $observer->getForm();
        foreach ($form->getElements() as $element) {
            /** @var \Magento\Framework\Data\Form\Element\AbstractElement $element */
            if ($element->getId() == 'action_fieldset') {
                $element->addField(
                    'simple_free_shipping',
                    'select',
                    [
                        'label' => __('Free Shipping'),
                        'title' => __('Free Shipping'),
                        'name' => 'simple_free_shipping',
                        'options' => [
                            0 => __('No'),
                            Rule::FREE_SHIPPING_ITEM => __('For matching items only'),
                            Rule::FREE_SHIPPING_ADDRESS => __('For shipment with matching items'),
                        ]
                    ]
                );
            }
        }
    }
}
