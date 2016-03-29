<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Category\Edit;

use Magento\Ui\Test\Block\Adminhtml\FormSections;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Category container block.
 */
class CategoryForm extends FormSections
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
     * @return FormSections
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null)
    {
        if ($fixture->hasData('store_id')) {
            $store = $fixture->getStoreId();
            $this->browser->find($this->header)->hover();
            $storeSwitcherBlock = $this->browser->find($this->storeSwitcherBlock);
            $storeSwitcherBlock->find($this->dropdownBlock, Locator::SELECTOR_CSS, 'liselectstore')->setValue($store);
            $modalElement = $this->browser->find($this->confirmModal);
            /** @var \Magento\Ui\Test\Block\Adminhtml\Modal $modal */
            $modal = $this->blockFactory->create('Magento\Ui\Test\Block\Adminhtml\Modal', ['element' => $modalElement]);
            $modal->acceptAlert();
        }
        return parent::fill($fixture, $element);
    }
}
