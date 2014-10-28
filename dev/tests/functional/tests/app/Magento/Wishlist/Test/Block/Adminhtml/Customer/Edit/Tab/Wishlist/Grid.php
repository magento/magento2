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
            'selector' => 'input[name="product_name"]'
        ],
        'qty_from' => [
            'selector' => 'input[name="qty[from]"]'
        ],
        'qty_to' => [
            'selector' => 'input[name="qty[to]"]'
        ],
        'options' => [
            'selector' => 'td//*[dt[contains(.,"%option_name%")]/following-sibling::dd[contains(.,"%value%")]]',
            'strategy' => 'xpath'
        ]
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
        if ($isStrict) {
            $rowTemplate = 'td[text()[normalize-space()="%s"]]';
        }
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
