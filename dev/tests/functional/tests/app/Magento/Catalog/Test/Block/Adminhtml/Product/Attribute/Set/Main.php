<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\Set;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

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
     * Selector for note block element.
     *
     * @var string
     */
    protected $noteBlock = '.attribute-set .title';

    /**
     * Selector for prompt.
     *
     * @var string
     */
    protected $promptModal = '.prompt._show[data-role=modal]';

    /**
     * Move Attribute to Attribute Group
     *
     * @param array $attributeData
     * @param string $attributeGroup
     * @return void
     */
    public function moveAttribute(array $attributeData, $attributeGroup = 'Product Details')
    {
        if (isset($attributeData['attribute_code'])) {
            $attribute = $attributeData['attribute_code'];
        } else {
            $attribute = strtolower($attributeData['frontend_label']);
        }

        $attributeGroupLocator = sprintf($this->groups, $attributeGroup);
        $target = $this->_rootElement->find($attributeGroupLocator, Locator::SELECTOR_XPATH);

        $target->click(); // Handle small resolution screen issue
        $this->browser->find($this->noteBlock)->click();

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
        $element = $this->browser->find($this->promptModal);
        /** @var \Magento\Ui\Test\Block\Adminhtml\Modal $modal */
        $modal = $this->blockFactory->create('Magento\Ui\Test\Block\Adminhtml\Modal', ['element' => $element]);
        $modal->setAlertText($groupName);
        $modal->acceptAlert();
    }
}
