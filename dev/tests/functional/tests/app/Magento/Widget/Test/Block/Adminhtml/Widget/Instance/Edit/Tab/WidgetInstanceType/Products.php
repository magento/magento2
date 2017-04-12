<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Block\Adminhtml\Widget\Instance\Edit\Tab\WidgetInstanceType;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Widget\Test\Block\Adminhtml\Widget\Instance\Edit\Tab\WidgetInstanceType\Product\Grid;

/**
 * Filling Product type layout.
 */
class Products extends WidgetInstanceForm
{
    /**
     * Product grid block.
     *
     * @var string
     */
    protected $productGrid = '//*[@class="chooser_container"]';

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
            $this->selectEntityInGrid($parametersFields['entities']);
        }
    }

    /**
     * Select entity in grid on layout tab.
     *
     * @param FixtureInterface $product
     * @return void
     */
    protected function selectEntityInGrid(FixtureInterface $product)
    {
        $this->_rootElement->find($this->chooser, Locator::SELECTOR_XPATH)->click();
        $this->getTemplateBlock()->waitLoader();

        /** @var Grid $productGrid */
        $productGrid = $this->blockFactory->create(
            \Magento\Widget\Test\Block\Adminhtml\Widget\Instance\Edit\Tab\WidgetInstanceType\Product\Grid::class,
            [
                'element' => $this->_rootElement
                    ->find($this->productGrid, Locator::SELECTOR_XPATH)
            ]
        );
        $productGrid->searchAndSelect(['name' => $product->getName()]);
        $this->getTemplateBlock()->waitLoader();
        if (!$this->clickOnElement($this->header, $this->apply, Locator::SELECTOR_CSS, Locator::SELECTOR_XPATH)) {
            $this->clickOnElement($this->footer, $this->apply, Locator::SELECTOR_CSS, Locator::SELECTOR_XPATH);
        }
    }
}
