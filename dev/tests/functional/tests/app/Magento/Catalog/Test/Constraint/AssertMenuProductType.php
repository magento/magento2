<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;

/**
 * Assert that order and filling of types on product page equals to incoming data.
 */
class AssertMenuProductType extends AbstractConstraint
{
    /**
     * Assert that order and filling of types on product page equals to incoming data.
     *
     * @param CatalogProductIndex $catalogProductIndex
     * @param array $menu
     * @return void
     */
    public function processAssert(CatalogProductIndex $catalogProductIndex, $menu = [])
    {
        $catalogProductIndex->open();
        ksort($menu);
        \PHPUnit_Framework_Assert::assertEquals(
            implode("\n", $menu),
            $catalogProductIndex->getGridPageActionBlock()->getTypeList(),
            'Order and filling of types on product page not equals to incoming data.'
        );
    }

    /**
     * Success message is displayed.
     *
     * @return string
     */
    public function toString()
    {
        return 'Order and filling of types on product page equals to incoming data.';
    }
}
