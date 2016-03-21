<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\EditGroup;
use Magento\Backend\Test\Page\Adminhtml\StoreIndex;
use Magento\Store\Test\Fixture\StoreGroup;
use Magento\Mtf\Constraint\AbstractAssertForm;

/**
 * Class AssertStoreGroupForm
 * Assert that displayed Store Group data on edit page equals passed from fixture
 */
class AssertStoreGroupForm extends AbstractAssertForm
{
    /**
     * Skipped fields for verify data
     *
     * @var array
     */
    protected $skippedFields = ['group_id'];

    /**
     * Assert that displayed Store Group data on edit page equals passed from fixture
     *
     * @param StoreIndex $storeIndex
     * @param EditGroup $editGroup
     * @param StoreGroup $storeGroup
     * @param StoreGroup $storeGroupOrigin [optional]
     * @return void
     */
    public function processAssert(
        StoreIndex $storeIndex,
        EditGroup $editGroup,
        StoreGroup $storeGroup,
        StoreGroup $storeGroupOrigin = null
    ) {
        $fixtureData = $storeGroupOrigin != null
            ? array_merge($storeGroupOrigin->getData(), $storeGroup->getData())
            : $storeGroup->getData();
        $storeIndex->open()->getStoreGrid()->searchAndOpenStoreGroup($storeGroup);
        $formData = $editGroup->getEditFormGroup()->getData();
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
        return 'Store Group data on edit page equals data from fixture.';
    }
}
