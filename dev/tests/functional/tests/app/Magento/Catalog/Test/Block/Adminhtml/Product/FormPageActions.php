<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product;

use Mtf\Page\BackendPage;
use Mtf\Client\Element\Locator;
use Mtf\Fixture\FixtureInterface;
use Magento\Backend\Test\Block\FormPageActions as ParentFormPageActions;

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
     * Save product form with window confirmation
     *
     * @param BackendPage $page
     * @param FixtureInterface $product
     * @return void
     */
    public function saveProduct(BackendPage $page, FixtureInterface $product)
    {
        parent::save();
        /** @var \Magento\Catalog\Test\Block\Adminhtml\Product\AffectedAttributeSetForm $affectedAttributeSetForm */
        $affectedAttributeSetForm = $page->getAffectedAttributeSetForm();
        $affectedAttributeSetForm->fill($product)->confirm();
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
