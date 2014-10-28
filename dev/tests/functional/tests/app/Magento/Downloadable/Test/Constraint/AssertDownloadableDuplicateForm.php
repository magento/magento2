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

namespace Magento\Downloadable\Test\Constraint;

use Mtf\Fixture\FixtureInterface;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Constraint\AssertProductDuplicateForm;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;

/**
 * Class AssertDownloadableDuplicateForm
 */
class AssertDownloadableDuplicateForm extends AssertProductDuplicateForm
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert form data equals duplicate product downloadable data
     *
     * @param FixtureInterface $product
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductEdit $productPage
     * @return void
     */
    public function processAssert(
        FixtureInterface $product,
        CatalogProductIndex $productGrid,
        CatalogProductEdit $productPage
    ) {
        $filter = ['sku' => $product->getSku() . '-1'];
        $productGrid->open()->getProductGrid()->searchAndOpen($filter);

        $formData = $productPage->getProductForm()->getData($product);
        $fixtureData = $this->convertDownloadableArray($this->prepareFixtureData($product->getData()));
        $errors = $this->verifyData($fixtureData, $formData);

        \PHPUnit_Framework_Assert::assertEmpty($errors, $errors);
    }

    /**
     * Sort downloadable array
     *
     * @param array $fields
     * @return array
     */
    protected function sortDownloadableArray(array $fields)
    {
        usort(
            $fields,
            function ($row1, $row2) {
                if ($row1['sort_order'] == $row2['sort_order']) {
                    return 0;
                }

                return ($row1['sort_order'] < $row2['sort_order']) ? -1 : 1;
            }
        );

        return $fields;
    }

    /**
     * Convert fixture array
     *
     * @param array $fields
     * @return array
     */
    protected function convertDownloadableArray(array $fields)
    {
        if (isset($fields['downloadable_links']['downloadable']['link'])) {
            $fields['downloadable_links']['downloadable']['link'] = $this->sortDownloadableArray(
                $fields['downloadable_links']['downloadable']['link']
            );
        }
        if (isset($fields['downloadable_sample']['downloadable']['sample'])) {
            $fields['downloadable_sample']['downloadable']['sample'] = $this->sortDownloadableArray(
                $fields['downloadable_sample']['downloadable']['sample']
            );
        }

        return $fields;
    }
}
