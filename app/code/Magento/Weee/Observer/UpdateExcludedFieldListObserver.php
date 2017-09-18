<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Observer;

use Magento\Framework\Event\ObserverInterface;

class UpdateExcludedFieldListObserver implements ObserverInterface
{
    /**
     * @var \Magento\Weee\Model\Tax
     */
    protected $weeeTax;

    /**
     * @param \Magento\Weee\Model\Tax $weeeTax
     */
    public function __construct(\Magento\Weee\Model\Tax $weeeTax)
    {
        $this->weeeTax = $weeeTax;
    }

    /**
     * Exclude WEEE attributes from standard form generation
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
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
