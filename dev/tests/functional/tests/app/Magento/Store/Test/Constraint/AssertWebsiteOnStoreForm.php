<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\NewGroupIndex;
use Magento\Backend\Test\Page\Adminhtml\StoreIndex;
use Magento\Store\Test\Fixture\Website;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertWebsiteOnStoreForm
 * Assert that Website visible on Store Group Form in Website dropdown
 */
class AssertWebsiteOnStoreForm extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that Website visible on Store Group Form in Website dropdown
     *
     * @param StoreIndex $storeIndex
     * @param NewGroupIndex $newGroupIndex
     * @param Website $website
     * @return void
     */
    public function processAssert(StoreIndex $storeIndex, NewGroupIndex $newGroupIndex, Website $website)
    {
        $websiteName = $website->getName();
        $storeIndex->open()->getGridPageActions()->createStoreGroup();
        \PHPUnit\Framework\Assert::assertTrue(
            $newGroupIndex->getEditFormGroup()->isWebsiteVisible($websiteName),
            'Website \'' . $websiteName . '\' is not present on Store Group Form in Website dropdown.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Website is visible on Store Group Form in Website dropdown.';
    }
}
