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

namespace Magento\Bundle\Test\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle;

use Mtf\Block\Block;
use Mtf\Client\Element;
use Mtf\Factory\Factory;
use Mtf\Client\Element\Locator;

/**
 * Class Option
 * Bundle product options block
 */
class Option extends Block
{
    /**
     * Grid to assign products to bundle option
     *
     * @var string
     */
    protected $searchGridBlock = "ancestor::body//div[contains(@style,'display: block') and @role='dialog']";

    /**
     * Added product row
     *
     * @var string
     */
    protected $selectionBlock = '#bundle_selection_row';

    /**
     * 'Add Products to Option' button
     *
     * @var string
     */
    protected $addProducts = '[data-ui-id$=add-selection-button]';

    /**
     * Bundle option toggle
     *
     * @var string
     */
    protected $optionToggle = '[data-target$=content]';

    /**
     * Bundle option title
     *
     * @var string
     */
    protected $title = '[name$="[title]"]';

    /**
     * Bundle option type
     *
     * @var string
     */
    protected $type = '[name$="[type]"]';

    /**
     * Determine whether bundle options is require to fill
     *
     * @var string
     */
    protected $required = '#field-option-req';

    /**
     * Get grid for assigning products for bundle option
     *
     * @return \Magento\Bundle\Test\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option\Search\Grid
     */
    protected function getSearchGridBlock()
    {
        return Factory::getBlockFactory()->getMagentoBundleAdminhtmlCatalogProductEditTabBundleOptionSearchGrid(
            $this->_rootElement->find($this->searchGridBlock, Locator::SELECTOR_XPATH)
        );
    }

    /**
     * Get product row assigned to bundle option
     *
     * @param int $rowNumber
     * @param Element $context
     * @return \Magento\Bundle\Test\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option\Selection
     */
    protected function getSelectionBlock($rowNumber, Element $context = null)
    {
        $element = $context !== null ? $context : $this->_rootElement;
        return Factory::getBlockFactory()->getMagentoBundleAdminhtmlCatalogProductEditTabBundleOptionSelection(
            $element->find($this->selectionBlock . '_' . $rowNumber)
        );
    }

    /**
     * Expand block
     *
     * @return void
     */
    public function expand()
    {
        if (!$this->_rootElement->find($this->title)->isVisible()) {
            $this->_rootElement->find($this->optionToggle)->click();
        }
    }

    /**
     * Fill bundle option
     *
     * @param array $fields
     * @param Element $context
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fillBundleOption(array $fields, Element $context)
    {
        $rowNumber = 0;
        $this->fillOptionData($fields);
        foreach ($fields['assigned_products'] as $field) {
            if (is_array($field)) {
                $this->_rootElement->find($this->addProducts)->click();
                $searchBlock = $this->getSearchGridBlock();
                $searchBlock->searchAndSelect($field['search_data']);
                $searchBlock->addProducts();
                $this->getSelectionBlock($rowNumber)->fillProductRow($field['data']);
                $rowNumber++;
            }
        }
    }

    /**
     * Update bundle option (now only general data, skipping assignments)
     *
     * @param array $fields
     * @return void
     */
    public function updateBundleOption(array $fields)
    {
        $this->fillOptionData($fields);
    }

    /**
     * Fill in general data to bundle option
     *
     * @param array $fields
     * @return void
     */
    private function fillOptionData(array $fields)
    {
        $this->_rootElement->find($this->title)->setValue($fields['title']['value']);
        $this->_rootElement->find($this->type, Locator::SELECTOR_CSS, 'select')->setValue($fields['type']['value']);
        $this->_rootElement->find($this->required, Locator::SELECTOR_CSS, 'checkbox')
            ->setValue($fields['required']['value']);
    }
}
