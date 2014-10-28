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

namespace Magento\Store\Test\Constraint;

use Mtf\Constraint\AbstractAssertForm;
use Magento\Store\Test\Fixture\Website;
use Magento\Backend\Test\Page\Adminhtml\StoreIndex;
use Magento\Backend\Test\Page\Adminhtml\EditWebsite;

/**
 * Class AssertWebsiteForm
 * Assert that displayed Website data on edit page equals passed from fixture
 */
class AssertWebsiteForm extends AbstractAssertForm
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

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
