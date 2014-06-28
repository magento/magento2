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

use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryEdit;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCategorySaveMessage
 * Assert that success message is displayed after category save
 */
class AssertCategorySaveMessage extends AbstractConstraint
{
    /**
     * Success category save message
     */
    const SUCCESS_MESSAGE = 'You saved the category.';

    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that success message is displayed after category save
     *
     * @param CatalogCategoryEdit $catalogCategoryEdit
     * @return void
     */
    public function processAssert(CatalogCategoryEdit $catalogCategoryEdit)
    {
        $actualMessage = $catalogCategoryEdit->getMessagesBlock()->getSuccessMessages();
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::SUCCESS_MESSAGE
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Success message is displayed
     *
     * @return string
     */
    public function toString()
    {
        return 'Success message is displayed.';
    }
}
