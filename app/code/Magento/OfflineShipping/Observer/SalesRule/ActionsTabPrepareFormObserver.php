<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflineShipping\Observer\SalesRule;

use Magento\OfflineShipping\Model\SalesRule\Rule;
use Magento\Framework\Event\ObserverInterface;

/**
 * Checkout cart shipping block plugin
 *
 * @author    Magento Core Team <core@magentocommerce.com>
 */
class ActionsTabPrepareFormObserver implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $observer->getForm();
        foreach ($form->getElements() as $element) {
            /** @var \Magento\Framework\Data\Form\Element\AbstractElement $element */
            if ($element->getId() != 'action_fieldset') {
                continue;
            }

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
