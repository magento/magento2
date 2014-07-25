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

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Websites;

use Mtf\Client\Element\Locator;
use Mtf\Client\Driver\Selenium\Element;

/**
 * Class StoreTree
 * Typified element class for store tree element
 */
class StoreTree extends Element
{
    /**
     * Selector for website checkbox
     *
     * @var string
     */
    protected $website = './/*[@class="website-name"]/label[contains(text(),"%s")]/../input';

    /**
     * Selector for selected website checkbox
     *
     * @var string
     */
    protected $selectedWebsite = './/*[@class="website-name"]/input[@checked="checked"][%d]/../label';

    /**
     * Set value
     *
     * @param array|string $values
     * @return void
     * @throws \Exception
     */
    public function setValue($values)
    {
        $values = is_array($values) ? $values : [$values];
        foreach ($values as $value) {
            $website = $this->find(sprintf($this->website, $value), Locator::SELECTOR_XPATH);
            if (!$website->isVisible()) {
                throw new \Exception("Can't find website: \"{$value}\".");
            }
            if (!$website->isSelected()) {
                $website->click();
            }
        }
    }

    /**
     * Get value
     *
     * @return array
     */
    public function getValue()
    {
        $values = [];

        $count = 1;
        $website = $this->find(sprintf($this->selectedWebsite, $count), Locator::SELECTOR_XPATH);
        while ($website->isVisible()) {
            $values[] = $website->getText();
            ++$count;
            $website = $this->find(sprintf($this->selectedWebsite, $count), Locator::SELECTOR_XPATH);
        }
        return $values;
    }
}
