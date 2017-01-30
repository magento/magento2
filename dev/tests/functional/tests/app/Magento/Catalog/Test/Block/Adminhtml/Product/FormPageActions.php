<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product;

use Magento\Backend\Test\Block\FormPageActions as ParentFormPageActions;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class FormAction
 * Form action
 */
class FormPageActions extends ParentFormPageActions
{
    /**
     * Save and create new product - 'Save & New'
     */
    const SAVE_NEW = 'new';

    /**
     * Save and create a duplicate product - 'Save & Duplicate'
     */
    const SAVE_DUPLICATE = 'duplicate';

    /**
     * Save and close the product page - 'Save & Close'
     */
    const SAVE_CLOSE = 'close';

    /**
     * CSS selector toggle "Save button"
     *
     * @var string
     */
    protected $toggleButton = '[data-ui-id="page-actions-toolbar-save-split-button-dropdown"]';

    /**
     * Save type item
     *
     * @var string
     */
    protected $saveTypeItem = '#save-split-button-%s-button';

    /**
     * "Save" button
     *
     * @var string
     */
    protected $saveButton = '#save-split-button-button';

    /**
     * Click on "Save" button
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
}
