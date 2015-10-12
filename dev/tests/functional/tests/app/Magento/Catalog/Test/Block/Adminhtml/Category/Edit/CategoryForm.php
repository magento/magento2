<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Category\Edit;

use Magento\Backend\Test\Block\Widget\FormTabs;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Factory\Factory;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Category container block.
 */
class CategoryForm extends FormTabs
{
    /**
     * Default sore switcher block locator.
     *
     * @var string
     */
    protected $storeSwitcherBlock = '.store-switcher';

    /**
     * Dropdown block locator.
     *
     * @var string
     */
    protected $dropdownBlock = '.dropdown';

    /**
     * Get Category edit form.
     *
     * @return \Magento\Catalog\Test\Block\Adminhtml\Category\Tab\ProductGrid
     */
    public function getCategoryProductsGrid()
    {
        return Factory::getBlockFactory()->getMagentoCatalogAdminhtmlCategoryTabProductGrid(
            $this->_rootElement->find($this->productsGridBlock)
        );
    }

    /**
     * Fill form with tabs.
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement|null $element
     * @return FormTabs
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null)
    {
        $tabs = $this->getFieldsByTabs($fixture);
        if ($fixture->hasData('store_id')) {
            $store = $fixture->getStoreId();
            $storeSwitcherBlock = $this->browser->find($this->storeSwitcherBlock);
            $storeSwitcherBlock->find($this->dropdownBlock, Locator::SELECTOR_CSS, 'liselectstore')->setValue($store);
            $this->browser->acceptAlert();

        }

        return $this->fillTabs($tabs, $element);
    }
}
