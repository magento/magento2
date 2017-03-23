<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Fixture\InjectableFixture;
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
     * @return $this
     */
    public function fill(FixtureInterface $product, SimpleElement $element = null, FixtureInterface $category = null)
    {
        $sections = $this->getFixtureFieldsByContainers($product);
        ksort($sections);

        if ($category) {
            $sections['product-details']['category_ids']['value'] = $category->getName();
        }

        return $this->fillContainers($sections, $element);
    }

    /**
     * Create data array for filling tabs.
     * Skip Advanced Price tab
     *
     * @param InjectableFixture $fixture
     * @return array
     */
    protected function getFixtureFieldsByContainers(InjectableFixture $fixture)
    {
        $sections = parent::getFixtureFieldsByContainers($fixture);
        if (isset($sections['advanced-pricing'])) {
            unset($sections['advanced-pricing']);
        }
        return $sections;
    }
}
