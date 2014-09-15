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

namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Super\Config;

use Mtf\Client\Driver\Selenium\Element;
use Mtf\Client\Element\Locator;
use Magento\Backend\Test\Block\Widget\Form;

/**
 * Class Matrix
 * Matrix row form
 */
class Matrix extends Form
{
    /**
     * Mapping for get optional fields
     *
     * @var array
     */
    protected $mappingGetFields = [
        'name' => [
            'selector' => 'td[data-column="name"] > a',
            'strategy' => Locator::SELECTOR_CSS
        ],
        'sku' => [
            'selector' => 'td[data-column="sku"] > span',
            'strategy' => Locator::SELECTOR_CSS
        ],
        'quantity_and_stock_status' => [
            'composite' => 1,
            'fields' => [
                'qty' => [
                    'selector' => 'td[data-column="qty"]',
                    'strategy' => Locator::SELECTOR_CSS
                ]
            ]
        ],
        'weight' => [
            'selector' => 'td[data-column="weight"]',
            'strategy' => Locator::SELECTOR_CSS
        ],
    ];

    /**
     * Selector for variation row by number
     *
     * @var string
     */
    protected $variationRowByNumber = './/tr[@data-role="row"][%d]';

    /**
     * Selector for variation row
     *
     * @var string
     */
    protected $variationRow = './/tr[@data-role="row"]';

    /**
     * Button for assign product to variation
     *
     * @var string
     */
    protected $configurableAttribute = 'td[data-column="name"] button.action-choose';

    // @codingStandardsIgnoreStart
    /**
     * Selector for row on product grid by product id
     *
     * @var string
     */
    protected $selectAssociatedProduct = '//ancestor::div[*[@id="associated-products-container"]]//td[@data-column="entity_id" and (contains(text(),"%s"))]';
    // @codingStandardsIgnoreEnd

    /**
     * Fill variations
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
            $mapping = $this->dataMapping($variation);

            $this->_fill($mapping, $variationRow);
            if (isset($variation['configurable_attribute'])) {
                $this->assignProduct($variationRow, $variation['configurable_attribute']);
            }

            ++$count;
        }
    }

    /**
     * Assign product to variation matrix
     *
     * @param Element $variationRow
     * @param int $productId
     * @return void
     */
    protected function assignProduct(Element $variationRow, $productId)
    {
        $variationRow->find($this->configurableAttribute)->click();
        $this->_rootElement->find(
            sprintf($this->selectAssociatedProduct, $productId),
            Locator::SELECTOR_XPATH
        )->click();
    }

    /**
     * Get variations data
     *
     * @return array
     */
    public function getVariationsData()
    {
        $data = [];
        $variationRows = $this->_rootElement->find($this->variationRow, Locator::SELECTOR_XPATH)->getElements();

        foreach ($variationRows as $key => $variationRow) {
            /** @var Element $variationRow */
            if ($variationRow->isVisible()) {
                $data[$key] = $this->_getData($this->dataMapping(), $variationRow);
                $data[$key] += $this->getOptionalFields($variationRow, $this->mappingGetFields);
            }
        }

        return $data;
    }

    /**
     * Get optional fields
     *
     * @param Element $context
     * @param array $fields
     * @return array
     */
    protected function getOptionalFields(Element $context, array $fields)
    {
        $data = [];

        foreach ($fields as $name => $params) {
            if (isset($params['composite']) && $params['composite']) {
                $data[$name] = $this->getOptionalFields($context, $params['fields']);
            } else {
                $data[$name] = $context->find($params['selector'], $params['strategy'])->getText();
            }
        }
        return $data;
    }
}
