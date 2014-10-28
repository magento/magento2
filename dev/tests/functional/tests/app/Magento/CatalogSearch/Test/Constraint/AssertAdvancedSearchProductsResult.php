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

namespace Magento\CatalogSearch\Test\Constraint;

use Mtf\Constraint\AbstractConstraint;
use Magento\CatalogSearch\Test\Page\AdvancedResult;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;

/**
 * Class AssertAdvancedSearchProductsResult
 */
class AssertAdvancedSearchProductsResult extends AbstractConstraint
{
    /**
     * Text for notice messages
     */
    const NOTICE_MESSAGE = "Don't see what you're looking for?";

    /**
     * Text for error messages
     */
    const ERROR_MESSAGE = 'No items were found using the following search criteria.';

    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'high';

    /**
     * Search results page
     *
     * @var AdvancedResult
     */
    protected $resultPage;

    /**
     * Fixture placeholder data
     *
     * @var array
     */
    protected $placeholder = [
        'tax_class_id' => 'tax_class',
    ];

    /**
     * Assert that Advanced Search result page contains only product(s) according to requested from fixture
     *
     * @param array $products
     * @param AdvancedResult $resultPage
     * @param array $productsSearch
     * @param CatalogProductSimple $productSearch
     * @return void
     */
    public function processAssert(
        array $products,
        AdvancedResult $resultPage,
        array $productsSearch,
        CatalogProductSimple $productSearch
    ) {
        $this->resultPage = $resultPage;
        $searchResult = [];
        foreach ($products as $key => $value) {
            if ($value === 'Yes') {
                /** @var CatalogProductSimple $productsSearch [$key] */
                $searchResult[$productsSearch[$key]->getSku()] = $productsSearch[$key];
            }
        }

        $errors = $this->checkSearchData($searchResult, $productSearch);
        foreach ($searchResult as $sku => $product) {
            /** @var CatalogProductSimple $product */
            $name = $product->getName();
            $isProductVisible = $resultPage->getListProductBlock()->isProductVisible($product->getName());
            while (!$isProductVisible && $resultPage->getBottomToolbar()->nextPage()) {
                $isProductVisible = $resultPage->getListProductBlock()->isProductVisible($product->getName());
            }
            if (!$isProductVisible) {
                $errors[] = '- failed to find the product (SKU - "'
                    . $sku . '", name - "' . $name . '") according to the search parameters';
            }
        }

        \PHPUnit_Framework_Assert::assertTrue(
            empty($errors),
            "The following errors occurred:\n" . implode("\n", $errors)
        );
    }

    /**
     * Validation page displaying the search data
     *
     * @param array $searchResult
     * @param CatalogProductSimple $productSearch
     * @return array
     */
    protected function checkSearchData(array $searchResult, CatalogProductSimple $productSearch)
    {
        $searchBlock = $this->resultPage->getSearchResultBlock();
        $errors = [];
        $textMessage = self::NOTICE_MESSAGE;
        if (empty($searchResult)) {
            $textMessage = self::ERROR_MESSAGE;
        }

        if (!$searchBlock->isVisibleMessages($textMessage)) {
            $errors[] = '- message does not match the search script';
        }

        $searchData = $searchBlock->getSearchSummaryItems();
        $productData = $this->prepareFixtureData($productSearch);
        foreach ($productData as $key => $data) {
            if (!isset($searchData[$key])) {
                $errors[] = '- "' . $key . '" not found on the page';
            } elseif ($searchData[$key] !== $data) {
                $errors[] = '- "' . $key . '" value does not match the page';
            }
        }

        return $errors;
    }

    /**
     * Preparation of fixture data before comparing
     *
     * @param CatalogProductSimple $productSearch
     * @return array
     */
    protected function prepareFixtureData(CatalogProductSimple $productSearch)
    {
        $compareData = [];
        foreach ($productSearch->getData() as $key => $value) {
            if ($key === 'price') {
                if (isset($value['price_from'])) {
                    $compareData[$key][] = $value['price_from'];
                }
                if (isset($value['price_to'])) {
                    $compareData[$key][] = $value['price_to'];
                }
            } else {
                $index = isset($this->placeholder[$key]) ? $this->placeholder[$key] : $key;
                $compareData[$index][] = $value;
            }
        }
        unset($compareData['url_key']);

        return $compareData;
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'All products are involved in the search were found successfully.';
    }
}
