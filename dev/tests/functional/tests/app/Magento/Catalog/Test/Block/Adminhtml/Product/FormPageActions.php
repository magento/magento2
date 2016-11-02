<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product;

use Magento\Backend\Test\Block\FormPageActions as ParentFormPageActions;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Product Form page actions.
 */
class FormPageActions extends ParentFormPageActions
{
    /**
     * Save and create new product - 'Save & New'.
     */
    const SAVE_NEW = '0';

    /**
     * Save and create a duplicate product - 'Save & Duplicate'.
     */
    const SAVE_DUPLICATE = '1';

    /**
     * Save and close the product page - 'Save & Close'.
     */
    const SAVE_CLOSE = '2';

    /**
     * CSS selector toggle "Save button".
     *
     * @var string
     */
    protected $toggleButton = '[data-ui-id="save-button-dropdown"]';

    /**
     * Save type item.
     *
     * @var string
     */
    protected $saveTypeItem = '[data-ui-id="save-button-item-%d"]';

    /**
     * "Save" button.
     *
     * @var string
     */
    protected $saveButton = '[data-ui-id="save-button"]';

    /**
     * "Add Attribute" button.
     *
     * @var string
     */
    private $addAttribute = '[data-ui-id="addattribute-button"]';

    /**
     * Magento form loader.
     *
     * @var string
     */
    private $spinner = '[data-role="spinner"]';

    /**
     * Default store switcher block locator.
     *
     * @var string
     */
    private $storeSwitcherBlock = '.store-switcher';

    /**
     * Dropdown block locator.
     *
     * @var string
     */
    private $dropdownBlock = '.dropdown';

    /**
     * Selector for confirm.
     *
     * @var string
     */
    private $confirmModal = '.confirm._show[data-role=modal]';

    /**
     * Click on "Save" button.
     *
     * @param FixtureInterface|null $product [optional]
     * @return void
     */
    public function save(FixtureInterface $product = null)
    {
        $typeId = null;

        if ($product) {
            $dataConfig = $product->getDataConfig();
            $typeId = isset($dataConfig['type_id']) ? $dataConfig['type_id'] : null;
        }

        if ($this->hasRender($typeId)) {
            $this->callRender($typeId, 'save', ['product' => $product]);
        } else {
            parent::save();
        }
    }

    /**
     * Click save and duplicate action
     *
     * @return void
     */
    public function saveAndDuplicate()
    {
        $this->_rootElement->find($this->toggleButton, Locator::SELECTOR_CSS)->click();
        $this->_rootElement->find(sprintf($this->saveTypeItem, static::SAVE_DUPLICATE), Locator::SELECTOR_CSS)->click();
    }

    /**
     * Click save and new action
     *
     * @return void
     */
    public function saveAndNew()
    {
        $this->_rootElement->find($this->toggleButton, Locator::SELECTOR_CSS)->click();
        $this->_rootElement->find(sprintf($this->saveTypeItem, static::SAVE_NEW), Locator::SELECTOR_CSS)->click();
    }

    /**
     * Click save and new action
     *
     * @return void
     */
    public function saveAndClose()
    {
        $this->_rootElement->find($this->toggleButton, Locator::SELECTOR_CSS)->click();
        $this->_rootElement->find(sprintf($this->saveTypeItem, static::SAVE_CLOSE), Locator::SELECTOR_CSS)->click();
    }

    /**
     * Click "Add Attribute" button.
     *
     * @return void
     */
    public function addNewAttribute()
    {
        $this->_rootElement->find($this->addAttribute)->click();
    }

    /**
     * Change Store View scope.
     *
     * @return void
     */
    public function changeStoreViewScope(FixtureInterface $store)
    {
        $this->waitForElementNotVisible($this->spinner);
        $this->waitForElementVisible($this->storeSwitcherBlock);
        $this->browser->find($this->storeSwitcherBlock)
            ->find($this->dropdownBlock, Locator::SELECTOR_CSS, 'liselectstore')
            ->setValue(sprintf('%s/%s', $store->getGroupId(), $store->getName()));
        $modalElement = $this->browser->find($this->confirmModal);
        /** @var \Magento\Ui\Test\Block\Adminhtml\Modal $modal */
        $modal = $this->blockFactory->create(
            \Magento\Ui\Test\Block\Adminhtml\Modal::class,
            ['element' => $modalElement]
        );
        $modal->acceptAlert();
    }
}
