<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Category\Edit;

use Magento\Backend\Test\Block\Widget\FormTabs;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;
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
     * Selector for confirm.
     *
     * @var string
     */
    protected $confirmModal = '.confirm._show[data-role=modal]';

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
            $modalElement = $this->browser->find($this->confirmModal);
            /** @var \Magento\Ui\Test\Block\Adminhtml\Modal $modal */
            $modal = $this->blockFactory->create('Magento\Ui\Test\Block\Adminhtml\Modal', ['element' => $modalElement]);
            $modal->acceptAlert();
        }

        return $this->fillTabs($tabs, $element);
    }

    /**
     * Return category Id.
     *
     * @return string
     */
    public function getCategoryId()
    {
        $categoryId = '';
        if (preg_match('/\/id\/(?<id>\d+)(?:\/)?/', $this->browser->getUrl(), $matches)) {
            $categoryId = $matches['id'];
        }

        return $categoryId;
    }
}
