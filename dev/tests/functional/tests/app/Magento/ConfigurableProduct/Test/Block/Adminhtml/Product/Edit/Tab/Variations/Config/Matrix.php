<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Variations\Config;

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
            'selector' => 'td[data-column="name"] > a',
            'strategy' => Locator::SELECTOR_CSS,
        ],
        'sku' => [
            'selector' => 'td[data-column="sku"]',
            'strategy' => Locator::SELECTOR_CSS,
        ],
        'price' => [
            'selector' => 'td[data-column="price"]',
            'strategy' => Locator::SELECTOR_CSS,
        ],
        'quantity_and_stock_status' => [
            'composite' => 1,
            'fields' => [
                'qty' => [
                    'selector' => 'td[data-column="qty"]',
                    'strategy' => Locator::SELECTOR_CSS,
                ],
            ],
        ],
        'weight' => [
            'selector' => 'td[data-column="weight"]',
            'strategy' => Locator::SELECTOR_CSS,
        ],
    ];

    /**
     * Selector for variation row by number.
     *
     * @var string
     */
    protected $variationRowByNumber = './/tr[@data-role="row"][%d]';

    /**
     * Selector for variation row.
     *
     * @var string
     */
    protected $variationRow = './/tr[@data-role="row"]';

    /**
     * Button for assign product to variation.
     *
     * @var string
     */
    protected $configurableAttribute = 'td[data-column="name"] button.action-choose';

    // @codingStandardsIgnoreStart
    /**
     * Selector for row on product grid by product id.
     *
     * @var string
     */
    protected $selectAssociatedProduct = '//ancestor::div[*[@id="associated-products-container"]]//td[@data-column="entity_id" and (contains(text(),"%s"))]';
    // @codingStandardsIgnoreEnd

    /**
     * Selector for template block.
     *
     * @var string
     */
    protected $template = './ancestor::body';

    /**
     * Action menu.
     *
     * @var string
     */
    private $actionMenu = '.action-select';

    /**
     * Choose a different Product button selector.
     *
     * @var string
     */
    private $chooseProduct = '[data-bind*="showGrid"]';

    /**
     * Selector for row on product grid by product id.
     *
     * @var string
     */
    private $associatedProductGrid =
        '[data-bind*="configurable_associated_product_listing.configurable_associated_product_listing"]';

    /**
     * Delete variation button selector.
     *
     * @var string
     */
    private $deleteVariation = '[data-bind*="removeProduct"]';

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
     * Assign product to variation matrix.
     *
     * @param SimpleElement $variationRow
     * @param string $productSku
     * @return void
     */
    protected function assignProduct(SimpleElement $variationRow, $productSku)
    {
        $variationRow->find($this->actionMenu)->hover();
        $variationRow->find($this->actionMenu)->click();
        $variationRow->find($this->chooseProduct)->click();
        $this->getTemplateBlock()->waitLoader();
        $this->getAssociatedProductGrid()->searchAndSelect(['sku' => $productSku]);
    }

    /**
     * Get variations data.
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
            \Magento\Backend\Test\Block\Template::class,
            ['element' => $this->_rootElement->find($this->template, Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Get products grid.
     *
     * @return \Magento\Ui\Test\Block\Adminhtml\DataGrid
     */
    public function getAssociatedProductGrid()
    {
        return $this->blockFactory->create(
            \Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\AssociatedProductGrid::class,
            ['element' => $this->browser->find($this->associatedProductGrid)]
        );
    }

    /**
     * Delete variations.
     *
     * @throws \Exception
     */
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
}
