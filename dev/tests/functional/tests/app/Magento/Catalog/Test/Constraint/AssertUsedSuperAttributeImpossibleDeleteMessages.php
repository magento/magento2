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

use Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductAttributeNew;

/**
 * Class AssertUsedSuperAttributeImpossibilityDeleteMessages
 * Assert that it's impossible to delete configurable attribute that is used in created configurable product
 */
class AssertUsedSuperAttributeImpossibleDeleteMessages extends AbstractConstraint
{
    /**
     * Impossible to delete message
     */
    const ERROR_DELETE_MESSAGE = 'This attribute is used in configurable products.';

    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'high';

    /**
     * Assert that it's impossible to delete configurable attribute that is used in created configurable product
     *
     * @param CatalogProductAttributeNew $newPage
     * @return void
     */
    public function processAssert(CatalogProductAttributeNew $newPage)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::ERROR_DELETE_MESSAGE,
            $newPage->getMessagesBlock()->getErrorMessages(),
            'Wrong impossible to delete message is not displayed.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Error delete message is present while deleting assigned configurable attribute.';
    }
}
