<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Action;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Backend\Test\Block\Widget\FormTabs;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Product attribute massaction edit page.
 */
class UpdateAttribute extends FormTabs
{
    /**
     * Selector for wainting tab content to load.
     *
     * @var string
     */
    protected $tabReadiness = '.admin__page-nav-item._active._loading';

    /**
     * Advanced Inventory Tab selector.
     *
     * @var string
     */
    protected $advancedInventoryTab = '#attributes_update_tabs_inventory';

    /**
     * Attributes Tab selector.
     *
     * @var string
     */
    protected $attributesTab = '#attributes_update_tabs_attributes';

    /**
     * Fill 'Advanced Inventory Properties' tab.
     *
     * @param array $fields
     * @param SimpleElement|null $element
     * @return $this
     */
    public function setFieldsData(array $fields, SimpleElement $element = null)
    {
        if (!$this->_rootElement->find($this->advancedInventoryTab)->isVisible()) {
            $this->_rootElement->find($this->advancedInventoryTab)->click();
        }

        return parent::fillTabs($fields, $element);
    }

    /**
     * Fill form with containers.
     *
     * @param FixtureInterface $product
     * @param array $checkbox [optional]
     * @return $this
     */
    public function fillForm(FixtureInterface $product, $checkbox = null)
    {
        $fields = $product->getData();
        foreach ($checkbox as $key => $value) {
            $fields[$key] = $value;
        }
        $fields = array_reverse($fields);
        $this->unassignedFields = $fields;
        $this->fillMissedFields();
    }
}
