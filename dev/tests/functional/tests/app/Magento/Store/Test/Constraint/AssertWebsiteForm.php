<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\EditWebsite;
use Magento\Backend\Test\Page\Adminhtml\StoreIndex;
use Magento\Store\Test\Fixture\Website;
use Magento\Mtf\Constraint\AbstractAssertForm;

/**
 * Class AssertWebsiteForm
 * Assert that displayed Website data on edit page equals passed from fixture
 */
class AssertWebsiteForm extends AbstractAssertForm
{
    /**
     * Skipped fields for verify data
     *
     * @var array
     */
    protected $skippedFields = ['website_id'];

    /**
     * Assert that displayed Website data on edit page equals passed from fixture
     *
     * @param StoreIndex $storeIndex
     * @param EditWebsite $editWebsite
     * @param Website $website
     * @return void
     */
    public function processAssert(
        StoreIndex $storeIndex,
        EditWebsite $editWebsite,
        Website $website
    ) {
        $fixtureData = $website->getData();
        $storeIndex->open()->getStoreGrid()->searchAndOpenWebsite($website);
        $formData = $editWebsite->getEditFormWebsite()->getData();
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
        return 'Website data on edit page equals data from fixture.';
    }
}
