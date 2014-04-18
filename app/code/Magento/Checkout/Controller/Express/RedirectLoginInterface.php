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
namespace Magento\Checkout\Controller\Express;

interface RedirectLoginInterface
{
    /**
     * Returns a list of action flags [flag_key] => boolean
     * @return array
     */
    public function getActionFlagList();

    /**
     * Returns before_auth_url redirect parameter for customer session
     * @return string|null
     */
    public function getCustomerBeforeAuthUrl();

    /**
     * Returns login url parameter for redirect
     * @return string|null
     */
    public function getLoginUrl();

    /**
     * Returns action name which requires redirect
     * @return string|null
     */
    public function getRedirectActionName();

    /**
     * Retrieve response object
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function getResponse();
}
