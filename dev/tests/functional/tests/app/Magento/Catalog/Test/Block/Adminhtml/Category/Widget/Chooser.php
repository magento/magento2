<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Category\Widget;

use Mtf\Block\Block;
use Mtf\Client\Element\Locator;

/**
 * Class CategoryChooser
 * Backend Cms Page select category block
 */
class Chooser extends Block
{
    /**
     * Category name selector
     *
     * @var string
     */
    protected $categoryNameSelector = "//a/span[contains(text(),'%s')]";

    /**
     * Select category by name
     *
     * @param string $name
     * @return void
     */
    public function selectCategoryByName($name)
    {
        $this->_rootElement->find(sprintf($this->categoryNameSelector, $name), Locator::SELECTOR_XPATH)->click();
    }
}
