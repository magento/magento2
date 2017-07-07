<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\AdvancedPricing;

use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\Options\AbstractOptions;
use Magento\Customer\Test\Fixture\CustomerGroup;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;

/**
 * Form 'Tier prices' on the 'Advanced Pricing' tab.
 */
class OptionTier extends AbstractOptions
{
    /**
     * 'Add Tier' button selector.
     *
     * @var string
     */
    protected $buttonFormLocator = '[data-action="add_new_row"]';

    /**
     * Locator for Customer Group element.
     *
     * @var string
     */
    protected $customerGroup = '//*[contains(@name, "[cust_group]")]';

    /**
     * Locator for Products Tier Price Rows.
     *
     * @var string
     */
    private $tierPriceRows = ".//*[@data-index='tier_price']/tbody/tr";

    /**
     * Fill product form 'Tier price'.
     *
     * @param array $fields
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fillOptions(array $fields, SimpleElement $element = null)
    {
        foreach ($fields['value'] as $key => $option) {
            $this->_rootElement->find($this->buttonFormLocator)->click();
            ++$key;
            parent::fillOptions($option, $element->find('tbody tr:nth-child(' . $key . ')'));
        }

        return $this;
    }

    /**
     * Get data options from 'Tier price' form.
     *
     * @param array $fields
     * @param SimpleElement|null $element
     * @return array
     */
    public function getDataOptions(array $fields = null, SimpleElement $element = null)
    {
        $data = [];
        if (isset($fields['value']) && is_array($fields['value'])) {
            foreach ($fields['value'] as $key => $option) {
                $data[$key++] = parent::getDataOptions($option, $element->find('tbody tr:nth-child(' . $key . ')'));
            }
        }

        return $data;
    }

    /**
     * Get tier price rows data.
     *
     * @param array|null $fields
     * @param SimpleElement|null $element
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getFieldsData($fields = null, SimpleElement $element = null)
    {
        $fieldsData = [];
        $rows = $this->_rootElement->getElements($this->tierPriceRows, Locator::SELECTOR_XPATH);

        if (null !== $rows) {
            foreach ($rows as $row) {
                $data = $this->dataMapping($fields);
                if ($row->find($data['value_type']['selector'])->getValue() == "fixed") {
                    unset($data['percentage_value']);
                }
                $fieldsData[] = $this->_getData($data, $row);
            }
        }

        return $fieldsData;
    }

    /**
     * Check whether Customer Group is visible.
     *
     * @param CustomerGroup $customerGroup
     * @return bool
     */
    public function isVisibleCustomerGroup(CustomerGroup $customerGroup)
    {
        $this->_rootElement->find($this->buttonFormLocator)->click();

        $options = $this->_rootElement->find($this->customerGroup, Locator::SELECTOR_XPATH)->getText();
        return false !== strpos($options, $customerGroup->getCustomerGroupCode());
    }

    /**
     * Checking group price options is visible.
     *
     * @return bool
     */
    public function hasGroupPriceOptions()
    {
        return $this->_rootElement->find('tbody tr')->isPresent();
    }

    /**
     * Waiting until advanced price form becomes hidden
     *
     * @return void
     */
    public function waitTierPriceFormLocks()
    {
        $this->_rootElement->waitUntil(
            function () {
                return $this->isVisible() ? null : true;
            }
        );
    }
}
