<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\LayeredNavigation\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Catalog layered navigation view block
 */
class Navigation extends Block
{
    /**
     * 'Clear All' link.
     *
     * @var string
     */
    protected $clearAll = '.action.clear';

    /**
     * Price range.
     *
     * @var string
     */
    protected $priceRange = "[href$='?price=%s']";

    /**
     * Attribute option.
     *
     * @var string
     */
    protected $attributeOption = "//a[contains(text(), '%s')]";

    /**
     * Attribute option title selector.
     *
     * @var string
     */
    protected $optionTitle = '.filter-options-title';

    /**
     * Attribute option content selector.
     *
     * @var string
     */
    protected $optionContent = '.filter-options-content';

    /**
     * Click on 'Clear All' link.
     *
     * @return void
     */
    public function clearAll()
    {
        $this->_rootElement->find($this->clearAll, locator::SELECTOR_CSS)->click();
    }

    /**
     * Select product price range.
     *
     * @param string $range
     * @return void
     */
    public function selectPriceRange($range)
    {
        $this->_rootElement->find(sprintf($this->priceRange, $range))->click();
    }

    /**
     * Select attribute option.
     *
     * @param string $optionName
     * @return void
     */
    public function selectAttributeOption($optionName)
    {
        $this->_rootElement->find(sprintf($this->attributeOption, $optionName), Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Get array of available filters.
     *
     * @return array
     */
    public function getFilters()
    {
        $options = $this->_rootElement->getElements($this->optionTitle);
        $data = [];
        foreach ($options as $option) {
            $data[] = $option->getText();
        }
        return $data;
    }
}
