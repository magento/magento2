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

namespace Magento\Catalog\Test\Constraint;

use Mtf\Constraint\AbstractAssertForm;
use Mtf\Fixture\FixtureInterface;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;

/**
 * Class AssertProductForm
 */
class AssertProductForm extends AbstractAssertForm
{
    /**
     * List skipped fixture fields in verify
     *
     * @var array
     */
    protected $skippedFixtureFields = [
        'id',
        'checkout_data'
    ];

    /**
     * Sort fields for fixture and form data
     *
     * @var array
     */
    protected $sortFields = [
        'custom_options::title'
    ];

    /**
     * Formatting options for array values
     *
     * @var array
     */
    protected $specialArray = [];

    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert form data equals fixture data
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
        $filter = ['sku' => $product->getSku()];
        $productGrid->open();
        $productGrid->getProductGrid()->searchAndOpen($filter);

        $productData = $product->getData();
        if ($product->hasData('custom_options')) {
            $customOptionsSource = $product->getDataFieldConfig('custom_options')['source'];
            $productData['custom_options'] = $customOptionsSource->getCustomOptions();
        }
        $fixtureData = $this->prepareFixtureData($productData, $this->sortFields);
        $formData = $this->prepareFormData($productPage->getProductForm()->getData($product), $this->sortFields);
        $error = $this->verifyData($fixtureData, $formData);
        \PHPUnit_Framework_Assert::assertTrue(empty($error), $error);
    }

    /**
     * Prepares fixture data for comparison
     *
     * @param array $data
     * @param array $sortFields [optional]
     * @return array
     */
    protected function prepareFixtureData(array $data, array $sortFields = [])
    {
        $data = array_diff_key($data, array_flip($this->skippedFixtureFields));

        if (isset($data['website_ids']) && !is_array($data['website_ids'])) {
            $data['website_ids'] = [$data['website_ids']];
        }
        if (!empty($this->specialArray)) {
            $data = $this->prepareSpecialPriceArray($data);
        }

        foreach ($sortFields as $path) {
            $data = $this->sortDataByPath($data, $path);
        }
        return $data;
    }

    /**
     * Prepare special price array for product
     *
     * @param array $fields
     * @return array
     */
    protected function prepareSpecialPriceArray(array $fields)
    {
        foreach ($this->specialArray as $key => $value) {
            if (array_key_exists($key, $fields)) {
                if (isset($value['type']) && $value['type'] == 'date') {
                    $fields[$key] = vsprintf('%d/%d/%d', explode('/', $fields[$key]));
                }
            }
        }
        return $fields;
    }

    /**
     * Prepares form data for comparison
     *
     * @param array $data
     * @param array $sortFields [optional]
     * @return array
     */
    protected function prepareFormData(array $data, array $sortFields = [])
    {
        foreach ($sortFields as $path) {
            $data = $this->sortDataByPath($data, $path);
        }
        return $data;
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Form data equal the fixture data.';
    }
}
