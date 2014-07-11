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

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Attributes;

use Mtf\Client\Element;
use Mtf\Client\Driver\Selenium\Element\SuggestElement;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;

/**
 * Class FormAttributeSearch
 * Form Attribute Search on Product page
 */
class Search extends SuggestElement
{
    /**
     * Attribute Set locator
     *
     * @var string
     */
    protected $value = '.action-toggle > span';

    /**
     * Attribute Set button
     *
     * @var string
     */
    protected $actionToggle = '.action-toggle';

    /**
     * Search attribute result locator
     *
     * @var string
     */
    protected $searchResult = '.mage-suggest-dropdown .ui-corner-all';

    /**
     * Set value
     *
     * @param string $value
     * @return void
     */
    public function setValue($value)
    {
        $this->find($this->actionToggle)->click();
        parent::setValue($value);
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->find($this->value)->getText();
    }

    /**
     * Checking not exist attribute in search result
     *
     * @param CatalogProductAttribute $productAttribute
     * @return bool
     */
    public function isExistAttributeInSearchResult($productAttribute)
    {
        $this->find($this->actionToggle)->click();
        $this->find($this->suggest)->setValue($productAttribute->getFrontendLabel());
        $this->waitResult();
        if ($this->find($this->searchResult)->getText() == $productAttribute->getFrontendLabel()) {
            return true;
        }
        return false;
    }
}
