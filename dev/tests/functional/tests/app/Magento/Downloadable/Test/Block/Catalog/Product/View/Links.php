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

namespace Magento\Downloadable\Test\Block\Catalog\Product\View;

use Mtf\Block\Block;
use Mtf\Client\Element\Locator;

/**
 * Class Links
 *
 * Downloadable links blocks on frontend
 */
class Links extends Block
{
    /**
     * Selector title for for links
     *
     * @var string
     */
    protected $titleForLink = '//div[contains(@class,"field downloads")]/label[@class="label"]/span';

    /**
     * Format for downloadable links list selector
     *
     * @var string
     */
    protected $linksListSelector = '//*[@id="downloadable-links-list"]/div[%d]/';

    /**
     * Title selector item links
     *
     * @var string
     */
    protected $titleForList = "label[@class='label']/span[1]";

    /**
     * Price selector item links
     *
     * @var string
     */
    protected $priceForList = 'label/span[contains(@class,"price-container")]//span[@class="price"]';

    /**
     * Checkbox selector item links
     *
     * @var string
     */
    protected $separatelyForList = "input[@type='checkbox']";

    /**
     * Change format downloadable links list
     *
     * @param int $index
     * @return string
     */
    protected function formatIndex($index)
    {
        return sprintf($this->linksListSelector, $index);
    }

    /**
     * Get title for links block
     *
     * @return string
     */
    public function getTitleForLinkBlock()
    {
        return $this->_rootElement->find($this->titleForLink, Locator::SELECTOR_XPATH)->getText();
    }

    /**
     * Get title for item link on data list
     *
     * @param int $index
     * @return string
     */
    public function getItemTitle($index)
    {
        return $this->_rootElement->find($this->formatIndex($index) . $this->titleForList, Locator::SELECTOR_XPATH)
            ->getText();
    }

    /**
     * Visible checkbox for item link on data list
     *
     * @param int $index
     * @return bool
     */
    public function isVisibleItemCheckbox($index)
    {
        return $this->_rootElement->find($this->formatIndex($index) . $this->separatelyForList, Locator::SELECTOR_XPATH)
            ->isVisible();
    }

    /**
     * Get price for item link on data list
     *
     * @param int $index
     * @return string
     */
    public function getItemPrice($index)
    {
        return $this->_rootElement->find($this->formatIndex($index) . $this->priceForList, Locator::SELECTOR_XPATH)
            ->getText();
    }
}
