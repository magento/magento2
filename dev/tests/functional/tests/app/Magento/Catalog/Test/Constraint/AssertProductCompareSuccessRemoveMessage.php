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

namespace Magento\Catalog\Test\Constraint;

use Mtf\Fixture\FixtureInterface;
use Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Page\Product\CatalogProductCompare;

/**
 * Class AssertProductCompareSuccessRemoveMessage
 * Assert message is appeared on "Compare Products" block on myAccount page
 */
class AssertProductCompareSuccessRemoveMessage extends AbstractConstraint
{
    const SUCCESS_MESSAGE = 'You removed product %s from the comparison list.';

    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert message is appeared on "Compare Products" block on myAccount page
     *
     * @param CatalogProductCompare $catalogProductCompare
     * @param FixtureInterface $product
     * @return void
     */
    public function processAssert(CatalogProductCompare $catalogProductCompare, FixtureInterface $product)
    {
        $successMessage = sprintf(self::SUCCESS_MESSAGE, $product->getName());
        $actualMessage = $catalogProductCompare->getMessagesBlock()->getSuccessMessages();
        \PHPUnit_Framework_Assert::assertEquals($successMessage, $actualMessage, 'Wrong success message is displayed.');
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Product has been removed from compare products list.';
    }
}
