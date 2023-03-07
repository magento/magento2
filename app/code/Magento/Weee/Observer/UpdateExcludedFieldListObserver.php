<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Weee\Model\Tax;

class UpdateExcludedFieldListObserver implements ObserverInterface
{
    /**
     * @param Tax $weeeTax
     */
    public function __construct(
        protected Tax $weeeTax
    ) {
    }

    /**
     * Exclude WEEE attributes from standard form generation
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        //adminhtml_catalog_product_form_prepare_excluded_field_list

        $block = $observer->getEvent()->getObject();
        $list = $block->getFormExcludedFieldList();
        $attributes = $this->weeeTax->getWeeeAttributeCodes(true);
        $list = array_merge($list, array_values($attributes));

        $block->setFormExcludedFieldList($list);

        return $this;
    }
}
