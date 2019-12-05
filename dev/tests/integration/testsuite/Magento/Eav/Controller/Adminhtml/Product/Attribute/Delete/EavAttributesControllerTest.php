<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Controller\Adminhtml\Product\Attribute\Delete;

use Magento\Catalog\Controller\Adminhtml\Product\Attribute\Delete\AbstractDeleteAttributeControllerTest;

/**
 * Delete catalog product attributes with input types like "date", "select", "multiselect", "textarea",
 * "texteditor", "text" and "boolean". Attributes from Magento_Eav module.
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class EavAttributesControllerTest extends AbstractDeleteAttributeControllerTest
{
    /**
     * Assert that attribute with input type "date" will be deleted
     * after dispatch delete product attribute action.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_date_attribute.php
     *
     * @return void
     */
    public function testDeleteDateAttribute(): void
    {
        $this->dispatchDeleteAttribute('date_attribute');
    }

    /**
     * Assert that attribute with input type "select" will be deleted
     * after dispatch delete product attribute action.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_dropdown_attribute.php
     *
     * @return void
     */
    public function testDeleteSelectAttribute(): void
    {
        $this->dispatchDeleteAttribute('dropdown_attribute');
    }

    /**
     * Assert that attribute with input type "multiselect" will be deleted
     * after dispatch delete product attribute action.
     *
     * @magentoDataFixture Magento/Catalog/_files/multiselect_attribute.php
     *
     * @return void
     */
    public function testDeleteMultiselectAttribute(): void
    {
        $this->dispatchDeleteAttribute('multiselect_attribute');
    }

    /**
     * Assert that attribute with input type "textarea" will be deleted
     * after dispatch delete product attribute action.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_text_attribute.php
     *
     * @return void
     */
    public function testDeleteTextareaAttribute(): void
    {
        $this->dispatchDeleteAttribute('text_attribute');
    }

    /**
     * Assert that attribute with input type "texteditor" will be deleted
     * after dispatch delete product attribute action.
     *
     * @magentoDataFixture Magento/Eav/_files/product_texteditor_attribute.php
     *
     * @return void
     */
    public function testDeleteTextEditorAttribute(): void
    {
        $this->dispatchDeleteAttribute('text_editor_attribute');
    }

    /**
     * Assert that attribute with input type "text" will be deleted
     * after dispatch delete product attribute action.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_varchar_attribute.php
     *
     * @return void
     */
    public function testDeleteTextAttribute(): void
    {
        $this->dispatchDeleteAttribute('varchar_attribute');
    }

    /**
     * Assert that attribute with input type "boolean" will be deleted
     * after dispatch delete product attribute action.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_boolean_attribute.php
     *
     * @return void
     */
    public function testDeleteBooleanAttribute(): void
    {
        $this->dispatchDeleteAttribute('boolean_attribute');
    }
}
