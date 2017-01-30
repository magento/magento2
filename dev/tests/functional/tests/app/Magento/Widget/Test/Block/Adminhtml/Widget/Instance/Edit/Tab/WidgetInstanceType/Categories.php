<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Block\Adminhtml\Widget\Instance\Edit\Tab\WidgetInstanceType;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Filling Categories type layout.
 */
class Categories extends WidgetInstanceForm
{
    /**
     * Filling layout form.
     *
     * @param array $parametersFields
     * @param SimpleElement $element
     * @return void
     */
    public function fillForm(array $parametersFields, SimpleElement $element = null)
    {
        $element = $element === null ? $this->_rootElement : $element;
        $fields = $this->dataMapping(array_diff_key($parametersFields, ['entities' => '']));
        foreach ($fields as $key => $values) {
            $this->_fill([$key => $values], $element);
            $this->getTemplateBlock()->waitLoader();
        }
        if (isset($parametersFields['entities'])) {
            $this->selectCategory($parametersFields['entities'], $element);
        }
    }

    /**
     * Select category on layout tab.
     *
     * @param Category $category
     * @param SimpleElement $element
     * @return void
     */
    protected function selectCategory(Category $category, SimpleElement $element)
    {
        $this->_rootElement->find($this->chooser, Locator::SELECTOR_XPATH)->click();
        $this->getTemplateBlock()->waitLoader();
        $mapping = $this->dataMapping(['entities' => '']);
        $mapping['entities']['value'] = implode('/', $this->prepareFullCategoryPath($category));
        $this->_fill($mapping, $element);
        $this->getTemplateBlock()->waitLoader();
        $this->_rootElement->find($this->apply, Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Prepare category path.
     *
     * @param Category $category
     * @return array
     */
    protected function prepareFullCategoryPath(Category $category)
    {
        $path = [];
        $parentCategory = $category->hasData('parent_id')
            ? $category->getDataFieldConfig('parent_id')['source']->getParentCategory()
            : null;

        if ($parentCategory !== null) {
            $path = $this->prepareFullCategoryPath($parentCategory);
        }
        return array_filter(array_merge($path, [$category->getName()]));
    }
}
