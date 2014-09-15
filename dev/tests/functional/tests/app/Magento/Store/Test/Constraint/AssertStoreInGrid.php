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

use Magento\Store\Test\Fixture\Store;
use Mtf\Constraint\AbstractConstraint;
use Magento\Backend\Test\Page\Adminhtml\StoreIndex;

/**
 * Class AssertStoreInGrid
 * Assert that created Store View can be found in Stores grid
 */
class AssertStoreInGrid extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that created Store View can be found in Stores grid by name
     *
     * @param StoreIndex $storeIndex
     * @param Store $store
     * @return void
     */
    public function processAssert(StoreIndex $storeIndex, Store $store)
    {
        $storeName = $store->getName();
        $storeIndex->open()->getStoreGrid()->search(['store_title' => $storeName]);
        \PHPUnit_Framework_Assert::assertTrue(
            $storeIndex->getStoreGrid()->isStoreExists($storeName),
            'Store \'' . $storeName . '\' is not present in grid.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Store View is present in grid.';
    }
}
