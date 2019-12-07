<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\CatalogSearch\Test\Page\CatalogsearchResult;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;

/**
 * Assert that product attribute is searchable on Frontend.
 */
class AssertAttributeSearchableByLabel extends AbstractConstraint
{
    /**
     * Assert that product attribute is searchable on Frontend.
     *
     * @param CatalogProductAttribute $attribute
     * @param CmsIndex $cmsIndex
     * @param InjectableFixture $product
     * @param CatalogsearchResult $catalogSearchResult
     * @return void
     */
    public function processAssert(
        CatalogProductAttribute $attribute,
        CmsIndex $cmsIndex,
        InjectableFixture $product,
        CatalogsearchResult $catalogSearchResult
    ) {
        $searchValue = $this->getSearchValue($attribute);

        $cmsIndex->open();
        $cmsIndex->getSearchBlock()->search($searchValue);

        do {
            $isVisible = $catalogSearchResult->getListProductBlock()->getProductItem($product)->isVisible();
        } while (!$isVisible && $catalogSearchResult->getBottomToolbar()->nextPage());

        \PHPUnit\Framework\Assert::assertTrue($isVisible, 'Product attribute is not searchable on Frontend.');
    }

    /**
     * Get search value for product attribute.
     *
     * @param CatalogProductAttribute $attribute
     * @return string
     */
    protected function getSearchValue(CatalogProductAttribute $attribute)
    {
        $searchValue = '';

        switch ($attribute->getFrontendInput()) {
            case 'Multiple Select':
            case 'Dropdown':
                foreach ($attribute->getOptions() as $option) {
                    if ($option['is_default'] == 'Yes') {
                        $searchValue = $option['admin'];
                    }
                }
                break;
            case 'Text Field':
                $searchValue = $attribute->getDefaultValueText();
                break;
            case 'Text Area':
                $searchValue = $attribute->getDefaultValueTextarea();
                break;
            case 'Date':
                $searchValue = $attribute->getDefaultValueDate();
                break;
            case 'Yes/No':
                $searchValue = $attribute->getDefaultValueYesno();
                break;
        }

        return $searchValue;
    }

    /**
     * Returns string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product attribute is searchable on Frontend.';
    }
}
