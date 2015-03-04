<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\StoreIndex;
use Magento\Backend\Test\Page\Adminhtml\StoreNew;
use Magento\Store\Test\Fixture\Store;
use Magento\Mtf\Constraint\AbstractAssertForm;

/**
 * Class AssertStoreForm
 * Assert that displayed Store View data on edit page equals passed from fixture
 */
class AssertStoreForm extends AbstractAssertForm
{
    /**
     * Assert that displayed Store View data on edit page equals passed from fixture
     *
     * @param StoreIndex $storeIndex
     * @param StoreNew $storeNew
     * @param Store $store
     * @return void
     */
    public function processAssert(
        StoreIndex $storeIndex,
        StoreNew $storeNew,
        Store $store
    ) {
        $storeIndex->open()->getStoreGrid()->searchAndOpenStore($store);
        $formData = $storeNew->getStoreForm()->getData();
        $fixtureData = $store->getData();
        $errors = $this->verifyData($fixtureData, $formData);
        \PHPUnit_Framework_Assert::assertEmpty($errors, $errors);
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Store View data on edit page equals data from fixture.';
    }
}
