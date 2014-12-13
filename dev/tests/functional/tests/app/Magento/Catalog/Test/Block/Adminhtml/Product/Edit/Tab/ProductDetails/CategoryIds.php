<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\ProductDetails;

use Mtf\Client\Driver\Selenium\Element\MultisuggestElement;
use Mtf\Client\Element\Locator;

/**
 * Class CategoryIds
 * Typified element class for category element
 */
class CategoryIds extends MultisuggestElement
{
    /**
     * Selector suggest input
     *
     * @var string
     */
    protected $suggest = '#category_ids-suggest';

    /**
     * Selector item of search result
     *
     * @var string
     */
    protected $resultItem = './/li/a/span[@class="category-label"][text()="%s"]';

    /**
     * Selector for click on top page.
     *
     * @var string
     */
    protected $top = './ancestor::body//*[@class="page-main-actions"]';

    /**
     * Set value
     *
     * @param array|string $values
     * @return void
     */
    public function setValue($values)
    {
        $this->find($this->top, Locator::SELECTOR_XPATH)->click();
        parent::setValue($values);
    }
}
