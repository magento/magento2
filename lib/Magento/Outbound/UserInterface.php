<?php
/**
 * The user is an abstraction for retrieving credentials for Authentication and validating Authorization
 *
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
 * @category    Magento
 * @package     Magento_Outbound
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Outbound;

interface UserInterface
{
    /**
     * Returns a shared secret known only by Magento and this user
     *
     * @return string a shared secret that both the user and Magento know about
     */
    public function getSharedSecret();

    /**
     * Checks whether this user has permission for the given topic
     *
     * @param string $topic topic to check
     * @return bool TRUE if permissions exist
     */
    public function hasPermission($topic);
}
