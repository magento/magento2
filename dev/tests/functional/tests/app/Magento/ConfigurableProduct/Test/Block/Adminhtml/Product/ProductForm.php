<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Backend\Test\Block\Widget\FormTabs;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Product creation form.
 */
class ProductForm extends \Magento\Catalog\Test\Block\Adminhtml\Product\ProductForm
{
    /**
     * Fill the product form.
     *
     * @param FixtureInterface $product
     * @param SimpleElement|null $element [optional]
     * @param FixtureInterface|null $category [optional]
     * @return FormTabs
     */
    public function fill(FixtureInterface $product, SimpleElement $element = null, FixtureInterface $category = null)
    {
        $tabs = $this->getFieldsByTabs($product);
        ksort($tabs);

        if ($category) {
            $tabs['product-details']['category_ids']['value'] = $category->getName();
        }

        $this->showAdvancedSettings();
        $this->getTab('variations')->showContent();
        return $this->fillTabs($tabs, $element);
    }
}
