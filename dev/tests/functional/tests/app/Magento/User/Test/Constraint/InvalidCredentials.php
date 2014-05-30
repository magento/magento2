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

use Mtf\Constraint\AbstractConstraint;
use Magento\Backend\Test\Page\AdminAuthLogin;

/**
 * Class InvalidCredentials
 */
class InvalidCredentials extends AbstractConstraint
{
    const INVALID_CREDENTIALS_MESSAGE = 'Please correct the user name or password.';

    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Verify incorrect credentials message while login to admin
     *
     * @param AdminAuthLogin $loginPage
     */
    public function processAssert(AdminAuthLogin $loginPage)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::INVALID_CREDENTIALS_MESSAGE,
            $loginPage->getMessagesBlock()->getErrorMessages(),
            'Message \'' . self::INVALID_CREDENTIALS_MESSAGE . '\' is not visible.'
        );
    }

    /**
     * @inheritdoc
     */
    public function toString()
    {
        return 'Invalid credentials message was displayed.';
    }
}
