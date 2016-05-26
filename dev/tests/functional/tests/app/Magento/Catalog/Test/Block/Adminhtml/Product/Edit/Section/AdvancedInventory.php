<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section;

use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Ui\Test\Block\Adminhtml\Section;

/**
 * Advanced inventory section.
 */
class AdvancedInventory extends Section
{
    /**
     * Selector for "Done" button.
     *
     * @var string
     */
    protected $doneButton = '.action-primary[data-role="action"]';

    /**
     * Locator for Advanced Inventory modal.
     *
     * @var string
     */
    protected $advancedInventoryRootElement = '.product_form_product_form_advanced_inventory_modal';

    /**
     * Fill data to fields on tab.
     *
     * @param array $fields
     * @param SimpleElement|null $contextElement
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setFieldsData(array $fields, SimpleElement $contextElement = null)
    {
        $context = $this->browser->find($this->advancedInventoryRootElement);
        parent::setFieldsData($fields, $context);
        $context->find($this->doneButton)->click();

        return $this;
    }

    /**
     * Get data of tab.
     *
     * @param array|null $fields
     * @param SimpleElement|null $element
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getFieldsData($fields = null, SimpleElement $element = null)
    {
        $context = $this->browser->find($this->advancedInventoryRootElement);
        $data = parent::getFieldsData($fields, $context);
        $context->find($this->doneButton)->click();

        return $data;
    }
}
