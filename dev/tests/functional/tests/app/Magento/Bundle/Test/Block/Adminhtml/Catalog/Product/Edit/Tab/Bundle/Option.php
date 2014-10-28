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

use Mtf\Block\Form;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;
use Magento\Bundle\Test\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option\Selection;
use Magento\Bundle\Test\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option\Search\Grid;

/**
 * Class Option
 * Bundle option block on backend
 */
class Option extends Form
{
    /**
     * Selector block Grid
     *
     * @var string
     */
    protected $searchGridBlock = "ancestor::body//div[contains(@style,'display: block') and @role='dialog']";

    /**
     * Added product row
     *
     * @var string
     */
    protected $selectionBlock = './/tr[contains(@id, "bundle_selection_row_")][not(@style="display: none;")][%d]';

    /**
     * Selector for 'Add Products to Option' button
     *
     * @var string
     */
    protected $addProducts = '[data-ui-id$=add-selection-button]';

    /**
     * Bundle option title
     *
     * @var string
     */
    protected $title = '[name$="[title]"]';

    /**
     * Remove selection button selector
     *
     * @var string
     */
    protected $removeSelection = 'button.delete';

    /**
     * Get grid for assigning products for bundle option
     *
     * @return Grid
     */
    protected function getSearchGridBlock()
    {
        return $this->blockFactory->create(
            'Magento\Bundle\Test\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option\Search\Grid',
            ['element' => $this->_rootElement->find($this->searchGridBlock, Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Get product row assigned to bundle option
     *
     * @param int $rowIndex
     * @return Selection
     */
    protected function getSelectionBlock($rowIndex)
    {
        return $this->blockFactory->create(
            'Magento\Bundle\Test\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option\Selection',
            ['element' => $this->_rootElement->find(sprintf($this->selectionBlock, $rowIndex), Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Fill bundle option
     *
     * @param array $fields
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fillOption(array $fields)
    {
        $mapping = $this->dataMapping($fields);
        $this->_fill($mapping);
        $selections = $this->_rootElement->find($this->removeSelection)->getElements();
        if (count($selections)) {
            foreach ($selections as $itemSelection) {
                $itemSelection->click();
            }
        }
        foreach ($fields['assigned_products'] as $key => $field) {
            $this->_rootElement->find($this->addProducts)->click();
            $searchBlock = $this->getSearchGridBlock();
            $searchBlock->searchAndSelect($field['search_data']);
            $searchBlock->addProducts();
            $this->getSelectionBlock(++$key)->fillProductRow($field['data']);
        }
    }

    /**
     * Get data bundle option
     *
     * @param array $fields
     * @return array
     */
    public function getOptionData(array $fields)
    {
        $mapping = $this->dataMapping($fields);
        $newField = $this->_getData($mapping);
        foreach ($fields['assigned_products'] as $key => $field) {
            $newField['assigned_products'][$key] = $this->getSelectionBlock($key + 1)->getProductRow($field['data']);
        }
        return $newField;
    }
}
