<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Constraint;

use Magento\Backend\Test\Fixture\GlobalSearch;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertGlobalSearchPreview
 * Assert that admin search preview is present in search results
 */
class AssertGlobalSearchPreview extends AbstractConstraint
{
    /**
     * Assert that admin search preview is present in search results
     *
     * @param Dashboard $dashboard
     * @param GlobalSearch $search
     * @return void
     */
    public function processAssert(Dashboard $dashboard, GlobalSearch $search)
    {
        $types = ['Products', 'Customers', 'Orders', 'Pages'];
        foreach ($types as $type) {
            $this->adminSearchAssert($dashboard, $search, $type);
        }
    }

    /**
     * Assert value of item in admin search preview
     *
     * @param Dashboard $dashboard
     * @param GlobalSearch $search
     * @param string $type
     */
    private function adminSearchAssert($dashboard, $search, $type)
    {
        $adminSearchPreview = '"' . $search->getQuery() . '" in '. $type;
        $isVisibleInResult = $dashboard->getAdminPanelHeader()->isAdminSearchPreviewVisible($adminSearchPreview, $type);
        \PHPUnit_Framework_Assert::assertTrue(
            $isVisibleInResult,
            'Search Preview for ' . $type . ' is not in search results'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Search preview is present in search results';
    }
}
