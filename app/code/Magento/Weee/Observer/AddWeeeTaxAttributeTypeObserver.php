<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Observer;

use Magento\Framework\Event\ObserverInterface;

class AddWeeeTaxAttributeTypeObserver implements ObserverInterface
{
    /**
     * Add new attribute type to manage attributes interface
     *
     * @param   \Magento\Framework\Event\Observer $observer
     * @return  $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // adminhtml_product_attribute_types

        $response = $observer->getEvent()->getResponse();
        $types = $response->getTypes();
        $types[] = [
            'value' => 'weee',
            'label' => __('Fixed Product Tax'),
            'hide_fields' => [
                'is_unique',
                'is_required',
                'frontend_class',
                '_scope',
                '_default_value',
                '_front_fieldset',
            ],
        ];

        $response->setTypes($types);

        return $this;
    }
}
