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

namespace Magento\GroupedProduct\Test\Block\Adminhtml\Product\Grouped;

use Mtf\Client\Element;
use Mtf\Client\Element\Locator;
use Magento\Backend\Test\Block\Widget\Tab;

/**
 * Class AssociatedProducts
 * Grouped products tab
 */
class AssociatedProducts extends Tab
{
    /**
     * 'Create New Option' button
     *
     * @var string
     */
    protected $addNewOption = '#grouped-product-container>button';

    /**
     * Associated products grid locator
     *
     * @var string
     */
    protected $productSearchGrid = "./ancestor::body//div[div[contains(@data-role,'add-product-dialog')]]";

    /**
     * Associated products list block
     *
     * @var string
     */
    protected $associatedProductsBlock = '[data-role=grouped-product-grid]';

    /**
     * Selector for delete button
     *
     * @var string
     */
    protected $deleteButton = '.delete';

    /**
     * Get search grid
     *
     * @return AssociatedProducts\Search\Grid
     */
    protected function getSearchGridBlock()
    {
        return $this->blockFactory->create(
            'Magento\GroupedProduct\Test\Block\Adminhtml\Product\Grouped\AssociatedProducts\Search\Grid',
            ['element' => $this->_rootElement->find($this->productSearchGrid, Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Get associated products list block
     *
     * @return AssociatedProducts\ListAssociatedProducts
     */
    protected function getListAssociatedProductsBlock()
    {
        return $this->blockFactory->create(
            'Magento\GroupedProduct\Test\Block\Adminhtml\Product\Grouped\AssociatedProducts\ListAssociatedProducts',
            ['element' => $this->_rootElement->find($this->associatedProductsBlock)]
        );
    }

    /**
     * Fill data to fields on tab
     *
     * @param array $fields
     * @param Element|null $element
     * @return $this
     */
    public function fillFormTab(array $fields, Element $element = null)
    {
        if (isset($fields['associated'])) {
            $options = $this->_rootElement->find($this->deleteButton)->getElements();
            if (count($options)) {
                foreach ($options as $option) {
                    $option->click();
                }
            }
            foreach ($fields['associated']['value']['assigned_products'] as $key => $groupedProduct) {
                $element->find($this->addNewOption)->click();
                $searchBlock = $this->getSearchGridBlock();
                $searchBlock->searchAndSelect(['name' => $groupedProduct['name']]);
                $searchBlock->addProducts();
                $this->getListAssociatedProductsBlock()->fillProductOptions($groupedProduct, ($key + 1));
            }
        }
        return $this;
    }

    /**
     * Get data to fields on group tab
     *
     * @param array|null $fields
     * @param Element|null $element
     * @return array
     */
    public function getDataFormTab($fields = null, Element $element = null)
    {
        $newFields = [];
        if (isset($fields['associated'])) {
            foreach ($fields['associated']['value']['assigned_products'] as $key => $groupedProduct) {
                $newFields['associated']['assigned_products'][$key] = $this->getListAssociatedProductsBlock()
                    ->getProductOptions($groupedProduct, ($key + 1));
            }
        }
        return $newFields;
    }
}
