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
 * @spi
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Theme\Test\Block;

use Mtf\Block\Block;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;

/**
 * Class Links
 * Page Top Links block
 */
class Links extends Block
{
    /**
     * Selector for qty products on compare
     *
     * @var string
     */
    protected $qtyCompareProducts = '.compare .counter.qty';

    /**
     * Link selector
     *
     * @var string
     */
    protected $link = '//a[contains(text(), "%s")]';

    /**
     * Open Link by title
     *
     * @param string $linkTitle
     * @return void
     */
    public function openLink($linkTitle)
    {
        $this->_rootElement->find(sprintf($this->link, $linkTitle), Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Is visible Link by title
     *
     * @param string $linkTitle
     * @return bool
     */
    public function isLinkVisible($linkTitle)
    {
        return $this->_rootElement->find(sprintf($this->link, $linkTitle), Locator::SELECTOR_XPATH)->isVisible();
    }

    /**
     * Get the number of products added to compare list
     *
     * @return string
     */
    public function getQtyInCompareList()
    {
        $this->waitForElementVisible($this->qtyCompareProducts);
        $compareProductLink = $this->_rootElement->find($this->qtyCompareProducts);
        preg_match_all('/^\d+/', $compareProductLink->getText(), $matches);
        return $matches[0][0];
    }

    /**
     * Get url from link
     *
     * @param string $linkTitle
     * @return string
     */
    public function getLinkUrl($linkTitle)
    {
        return trim($this->_rootElement->find(sprintf($this->link, $linkTitle), Locator::SELECTOR_XPATH)->getUrl());
    }
}
