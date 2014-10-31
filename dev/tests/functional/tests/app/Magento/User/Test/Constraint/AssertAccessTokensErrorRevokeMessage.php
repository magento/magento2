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

namespace Magento\User\Test\Constraint;

use Magento\User\Test\Page\Adminhtml\UserEdit;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertAccessTokensErrorRevokeMessage
 * Assert that error message appears after click on 'Force Sing-In' button for user without tokens.
 */
class AssertAccessTokensErrorRevokeMessage extends AbstractConstraint
{
    /**
     * User revoke tokens error message.
     */
    const ERROR_MESSAGE = 'This user has no tokens.';

    /**
     * Constraint severeness.
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that error message appears after click on 'Force Sing-In' button for user without tokens.
     *
     * @param UserEdit $userEdit
     * @return void
     */
    public function processAssert(UserEdit $userEdit)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::ERROR_MESSAGE,
            $userEdit->getMessagesBlock()->getErrorMessages()
        );
    }

    /**
     * Return string representation of object
     *
     * @return string
     */
    public function toString()
    {
        return self::ERROR_MESSAGE . ' error message is present on UserEdit page.';
    }
}
