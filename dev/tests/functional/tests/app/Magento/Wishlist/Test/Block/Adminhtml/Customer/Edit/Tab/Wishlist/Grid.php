<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Block\Adminhtml\Customer\Edit\Tab\Wishlist;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Class Grid
 * Grid on Wishlist tab in customer details on backend
 */
class Grid extends \Magento\Backend\Test\Block\Widget\Grid
{
    /**
     * Grid fields map
     *
     * @var array
     */
    protected $filters = [
        'product_name' => [
            'selector' => 'input[name="product_name"]',
        ],
        'qty_from' => [
            'selector' => 'input[name="qty[from]"]',
        ],
        'qty_to' => [
            'selector' => 'input[name="qty[to]"]',
        ],
        'options' => [
            'selector' => 'td//*[dt[contains(.,"%option_name%")]/following-sibling::dd[contains(.,"%value%")]]',
            'strategy' => 'xpath',
        ],
    ];

    /**
     * Delete link selector
     *
     * @var string
     */
    protected $deleteLink = 'a[onclick*="removeItem"]';

    /**
     * Configure link selector
     *
     * @var string
     */
    protected $configureLink = 'a[onclick*="configureItem"]';

    /**
     * Secondary part of row locator template for getRow() method with strict option.
     *
     * @var string
     */
    protected $rowTemplateStrict = 'td[contains(.,normalize-space("%s"))]';

    /**
     * Selector for confirm.
     *
     * @var string
     */
    protected $confirmModal = '.confirm._show[data-role=modal]';

    /**
     * Delete product
     *
     * @return void
     */
    protected function delete()
    {
        $this->_rootElement->find($this->rowItem . ' ' . $this->deleteLink)->click();
        $element = $this->browser->find($this->confirmModal);
        /** @var \Magento\Ui\Test\Block\Adminhtml\Modal $modal */
        $modal = $this->blockFactory->create('Magento\Ui\Test\Block\Adminhtml\Modal', ['element' => $element]);
        $modal->acceptAlert();
    }

    /**
     * Configure product
     *
     * @return void
     */
    protected function configure()
    {
        $this->_rootElement->find($this->rowItem . ' ' . $this->configureLink)->click();
    }

    /**
     * Search item product and action it
     *
     * @param array $filter
     * @param string $action
     * @return void
     */
    public function searchAndAction(array $filter, $action)
    {
        $this->search($filter);
        $this->{ucfirst($action)}();
        $this->waitLoader();
    }

    /**
     * Check if specific row exists in grid.
     *
     * @param array $filter
     * @param bool $isSearchable
     * @param bool $isStrict
     * @return bool
     */
    public function isRowVisible(array $filter, $isSearchable = true, $isStrict = true)
    {
        if (isset($filter['options'])) {
            unset($filter['options']);
        }
        return parent::isRowVisible($filter, $isSearchable, $isStrict);
    }

    /**
     * Obtain specific row in grid
     *
     * @param array $filter
     * @param bool $isStrict [optional]
     * @return SimpleElement
     */
    protected function getRow(array $filter, $isStrict = true)
    {
        $options = [];
        if (isset($filter['options'])) {
            $options = $filter['options'];
            unset($filter['options']);
        }
        $location = '//tr[';
        $rowTemplate = ($isStrict) ? $this->rowTemplateStrict : $this->rowTemplate;
        $rows = [];
        foreach ($filter as $value) {
            $rows[] = sprintf($rowTemplate, $value);
        }
        if (!empty($options) && is_array($options)) {
            foreach ($options as $value) {
                $rows[] = str_replace(
                    '%value%',
                    $value['value'],
                    str_replace('%option_name%', $value['option_name'], $this->filters['options']['selector'])
                );
            }
        }

        return $this->_rootElement->find($location . implode(' and ', $rows) . ']', Locator::SELECTOR_XPATH);
    }
}
