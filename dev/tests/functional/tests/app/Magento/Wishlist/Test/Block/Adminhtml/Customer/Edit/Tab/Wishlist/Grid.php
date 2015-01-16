<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Block\Adminhtml\Customer\Edit\Tab\Wishlist;

use Mtf\Client\Element;
use Mtf\Client\Element\Locator;

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
     * Delete product
     *
     * @return void
     */
    protected function delete()
    {
        $this->_rootElement->find($this->rowItem . ' ' . $this->deleteLink)->click();
        $this->_rootElement->acceptAlert();
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
     * Obtain specific row in grid
     *
     * @param array $filter
     * @param bool $isSearchable [optional]
     * @param bool $isStrict [optional]
     * @return Element
     */
    protected function getRow(array $filter, $isSearchable = true, $isStrict = true)
    {
        $options = [];
        $this->openFilterBlock();
        if (isset($filter['options'])) {
            $options = $filter['options'];
            unset($filter['options']);
        }
        if ($isSearchable) {
            $this->search($filter);
        }
        $location = '//div[@class="grid"]//tr[';
        $rowTemplate = 'td[contains(.,normalize-space("%s"))]';
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
        $location = $location . implode(' and ', $rows) . ']';
        return $this->_rootElement->find($location, Locator::SELECTOR_XPATH);
    }
}
