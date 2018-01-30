<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Section\Variations\Config;

use Magento\Mtf\Client\ElementInterface;
use Magento\Mtf\Client\Locator;
use Magento\Backend\Test\Block\Template;
use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Matrix row form.
 */
class Matrix extends Form
{
    /**
     * Mapping for get optional fields.
     *
     * @var array
     */
    protected $mappingGetFields = [
        'name' => [
            'selector' => 'td[data-index="name_container"] a',
            'strategy' => Locator::SELECTOR_CSS,
        ],
        'sku' => [
            'selector' => 'td[data-index="sku_container"] span[data-index="sku_text"]',
            'strategy' => Locator::SELECTOR_CSS,
        ],
        'price' => [
            'selector' => 'td[data-index="price_container"] span[data-index="price_text"]',
            'strategy' => Locator::SELECTOR_CSS,
        ],
        'qty' => [
            'selector' => 'td[data-index="quantity_container"] span[data-index="quantity_text"]',
            'strategy' => Locator::SELECTOR_CSS,
        ],
        'weight' => [
            'selector' => 'td[data-index="price_weight"] span[data-index="weight_text"]',
            'strategy' => Locator::SELECTOR_CSS,
        ],
    ];

    /**
     * Selector for variation row by number.
     *
     * @var string
     */
    protected $variationRowByNumber = './/tr[@class="data-row" or @class="data-row _odd-row"][%d]';

    /**
     * Selector for variation row.
     *
     * @var string
     */
    protected $variationRow = './/tr[contains(@class, "data-row")]';

    /**
     * Selector for row on product grid by product id.
     *
     * @var string
     */
    protected $associatedProductGrid =
        '[data-bind*="configurable_associated_product_listing.configurable_associated_product_listing"]';

    /**
     * Selector for template block.
     *
     * @var string
     */
    protected $template = './ancestor::body';

    /**
     * Delete variation button selector.
     *
     * @var string
     */
    protected $deleteVariation = '[data-bind*="Remove Product"]';

    /**
     * Choose a different Product button selector.
     *
     * @var string
     */
    protected $chooseProduct = '[data-bind*="openModalWithGrid"]';

    /**
     * Action menu
     *
     * @var string
     */
    protected $actionMenu = '.action-select';

    /**
     * Fill variations.
     *
     * @param array $matrix
     * @return void
     */
    public function fillVariations(array $matrix)
    {
        $count = 1;
        foreach ($matrix as $variation) {
            $variationRow = $this->_rootElement->find(
                sprintf($this->variationRowByNumber, $count),
                Locator::SELECTOR_XPATH
            );
            ++$count;

            if (isset($variation['configurable_attribute'])) {
                $this->assignProduct($variationRow, $variation['sku']);
                continue;
            }

            $mapping = $this->dataMapping($variation);
            $this->_fill($mapping, $variationRow);
        }
    }

    /**
     * Assign product to variation matrix
     *
     * @param ElementInterface $variationRow
     * @param string $productSku
     * @return void
     */
    protected function assignProduct(ElementInterface $variationRow, $productSku)
    {
        $variationRow->find($this->actionMenu)->hover();
        $variationRow->find($this->actionMenu)->click();
        $variationRow->find($this->chooseProduct)->click();
        $this->getTemplateBlock()->waitLoader();
        $this->getAssociatedProductGrid()->searchAndSelect(['sku' => $productSku]);
    }

    /**
     * Get variations data
     *
     * @return array
     */
    public function getVariationsData()
    {
        $data = [];
        $variationRows = $this->_rootElement->getElements($this->variationRow, Locator::SELECTOR_XPATH);

        foreach ($variationRows as $key => $variationRow) {
            /** @var SimpleElement $variationRow */
            if ($variationRow->isVisible()) {
                $data[$key] = $this->getDataFields($variationRow, $this->mappingGetFields);
            }
        }

        return $data;
    }

    /**
     * Get variation fields.
     *
     * @param SimpleElement $context
     * @param array $fields
     * @return array
     */
    protected function getDataFields(SimpleElement $context, array $fields)
    {
        $data = [];

        foreach ($fields as $name => $params) {
            if (isset($params['composite']) && $params['composite']) {
                $data[$name] = $this->getDataFields($context, $params['fields']);
            } else {
                $data[$name] = $context->find($params['selector'], $params['strategy'])->getText();
            }
        }
        return $data;
    }

    /**
     * Get template block.
     *
     * @return Template
     */
    public function getTemplateBlock()
    {
        return $this->blockFactory->create(
            'Magento\Backend\Test\Block\Template',
            ['element' => $this->_rootElement->find($this->template, Locator::SELECTOR_XPATH)]
        );
    }

    public function deleteVariations()
    {
        $rowLocator = sprintf($this->variationRowByNumber, 1);
        $variationText = '';
        while ($this->_rootElement->find($rowLocator, Locator::SELECTOR_XPATH)->isVisible()) {
            $variation = $this->_rootElement->find($rowLocator, Locator::SELECTOR_XPATH);
            if ($variationText == $variation->getText()) {
                throw new \Exception("Failed to delete configurable product variation");
            }
            $variationText = $variation->getText();
            $variation->find($this->actionMenu)->hover();
            $variation->find($this->actionMenu)->click();
            $variation->find($this->deleteVariation)->click();
        }
    }

    /**
     * @return \Magento\Ui\Test\Block\Adminhtml\DataGrid
     */
    public function getAssociatedProductGrid()
    {
        return $this->blockFactory->create(
            'Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\AssociatedProductGrid',
            ['element' => $this->browser->find($this->associatedProductGrid)]
        );
    }
}
