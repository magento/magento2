<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryEdit;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCategoryCheckDefaultUrl
 * Assert that category form contains proper values of the url key related fields after category save
 */
class AssertCategoryCheckDefaultUrl extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    protected $urlKeyRelatedFields = [
        'url_key',
        'use_default_url_key'
    ];

    /**
     * Assert that displayed category data on edit page correct
     *
     * @param CatalogCategoryEdit $catalogCategoryEdit
     * @param Category $category
     * @return void
     */
    public function processAssert(
        CatalogCategoryEdit $catalogCategoryEdit,
        Category $category
    ) {

        $catalogCategoryEdit->getEditForm()->openSection('seo');
        $formData = $catalogCategoryEdit->getEditForm()->getSection('seo')->getFieldsData();
        $formData = $this->prepareFormData($formData);
        $fixtureData = $this->prepareFixtureData($category->getData());
        \PHPUnit_Framework_Assert::assertEquals($fixtureData, $formData, 'Incorrect Url Key');
    }

    /**
     * Prepares form data for comparison.
     *
     * @param array $data
     * @return array
     */
    protected function prepareFormData(array $data)
    {
        $preparedData = [
            'url_key' => null,
            'use_default_url_key' => null
        ];
        if (!empty($data['url_key'])) {
            $preparedData['url_key'] = $data['url_key'];
        }
        if (!empty($data['use_default_url_key'])) {
            $preparedData['use_default_url_key'] = $data['use_default_url_key'];
        }
        return $preparedData;
    }

    /**
     * Prepares fixture data for comparison.
     *
     * @param array $data
     * @return array
     */
    protected function prepareFixtureData(array $data)
    {
        $preparedData = [
            'url_key' => null,
            'use_default_url_key' => 'yes'
        ];
        if (!empty($data['url_key'])) {
            $preparedData['url_key'] = $data['url_key'];
        }
        if (!empty($data['use_default_url_key'])) {
            $preparedData['use_default_url_key'] = $data['use_default_url_key'];
        }
        return $preparedData;
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Category URL key is correct';
    }
}
