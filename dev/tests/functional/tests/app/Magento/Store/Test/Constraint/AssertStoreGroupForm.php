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
use Magento\Store\Test\Fixture\StoreGroup;
use Magento\Backend\Test\Page\Adminhtml\StoreIndex;
use Magento\Backend\Test\Page\Adminhtml\EditGroup;

/**
 * Class AssertStoreGroupForm
 * Assert that displayed Store Group data on edit page equals passed from fixture
 */
class AssertStoreGroupForm extends AbstractAssertForm
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
