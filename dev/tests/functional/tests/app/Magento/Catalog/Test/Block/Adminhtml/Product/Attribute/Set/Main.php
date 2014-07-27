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

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\Set;

use Mtf\Block\Block;
use Mtf\Client\Element\Locator;

/**
 * Class Main
 * Attribute Set Main block
 */
class Main extends Block
{
    /**
     * Attribute Groups
     *
     * @var string
     */
    protected $groups = './/*[contains(@class,"x-tree-root-node")]//li[@class="x-tree-node"]/div/a/span[text()="%s"]';

    /**
     * Attribute that will be added to the group
     *
     * @var string
     */
    protected $attribute = './/*[contains(@class,"x-tree-root-node")]//div/a/span[text()="%s"]';

    /**
     * Attribute label locator
     *
     * @var string
     */
    protected $attributeLabel = ".//*[contains(@id,'tree-div2')]//li[@class='x-tree-node']/div/a/span[text()='%s']";

    /**
     * Add group button locator
     *
     * @var string
     */
    protected $addGroupButton = '[data-ui-id="adminhtml-catalog-product-set-edit-add-group-button"]';

    /**
     * Move Attribute to Attribute Group
     *
     * @param array $attributeData
     * @param string $attributeGroup
     * @return void
     */
    public function moveAttribute(array $attributeData, $attributeGroup)
    {
        if (isset($attributeData['attribute_code'])) {
            $attribute = $attributeData['attribute_code'];
        } else {
            $attribute = strtolower($attributeData['frontend_label']);
        }

        $attributeGroupLocator = sprintf($this->groups, $attributeGroup);
        $target = $this->_rootElement->find($attributeGroupLocator, Locator::SELECTOR_XPATH);

        $attributeLabelLocator = sprintf($this->attribute, $attribute);
        $this->_rootElement->find($attributeLabelLocator, Locator::SELECTOR_XPATH)->dragAndDrop($target);
    }

    /**
     * Get AttributeSet name from product_set edit page
     *
     * @return string
     */
    public function getAttributeSetName()
    {
        return $this->_rootElement->find("#attribute_set_name", Locator::SELECTOR_CSS)->getValue();
    }

    /**
     * Checks present Product Attribute on product_set Groups
     *
     * @param string $attributeLabel
     * @return bool
     */
    public function checkProductAttribute($attributeLabel)
    {
        $attributeLabelLocator = sprintf(
            ".//*[contains(@id,'tree-div1')]//li[@class='x-tree-node']/div/a/span[text()='%s']",
            $attributeLabel
        );

        return $this->_rootElement->find($attributeLabelLocator, Locator::SELECTOR_XPATH)->isVisible();
    }

    /**
     * Checks present Unassigned Product Attribute
     *
     * @param string $attributeLabel
     * @return bool
     */
    public function checkUnassignedProductAttribute($attributeLabel)
    {
        $attributeLabelLocator = sprintf($this->attributeLabel, $attributeLabel);

        return $this->_rootElement->find($attributeLabelLocator, Locator::SELECTOR_XPATH)->isVisible();
    }

    /**
     * Add attribute set group to Attribute Set
     *
     * @param string $groupName
     * @return void
     */
    public function addAttributeSetGroup($groupName)
    {
        $this->_rootElement->find($this->addGroupButton)->click();
        $this->_rootElement->setAlertText($groupName);
        $this->_rootElement->acceptAlert();
    }
}
