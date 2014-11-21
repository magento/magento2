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

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit;

use Magento\Backend\Test\Block\Widget\Tab;
use Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\Edit;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Mtf\Client\Element\Locator;

/**
 * General class for tabs on product FormTabs with "Add attribute" button.
 */
class ProductTab extends Tab
{
    /**
     * Attribute Search locator the Product page.
     *
     * @var string
     */
    protected $attributeSearch = "//div[contains(@data-role, '%s')]//*[@id='product-attribute-search-container']";

    /**
     * Selector for 'New Attribute' button.
     *
     * @var string
     */
    protected $newAttributeButton = '[id^="create_attribute"]';

    /**
     * Fixture mapping.
     *
     * @param array|null $fields
     * @param string|null $parent
     * @return array
     */
    protected function dataMapping(array $fields = null, $parent = null)
    {
        if (isset($fields['custom_attribute'])) {
            $this->placeholders = ['attribute_code' => $fields['custom_attribute']['value']['code']];
            $this->applyPlaceholders();
        }
        return parent::dataMapping($fields, $parent);
    }

    /**
     * Click on 'New Attribute' button.
     *
     * @param string $tabName
     * @return void
     */
    public function addNewAttribute($tabName)
    {
        $this->_rootElement->find(sprintf($this->attributeSearch, $tabName), Locator::SELECTOR_XPATH)->click();
        $this->_rootElement->find($this->newAttributeButton)->click();
    }
}
