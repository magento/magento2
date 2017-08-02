<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller\Express;

/**
 * Interface \Magento\Checkout\Controller\Express\RedirectLoginInterface
 *
 * @since 2.0.0
 */
interface RedirectLoginInterface
{
    /**
     * Returns a list of action flags [flag_key] => boolean
     * @return array
     * @since 2.0.0
     */
    public function getActionFlagList();

    /**
     * Returns before_auth_url redirect parameter for customer session
     * @return string|null
     * @since 2.0.0
     */
    public function getCustomerBeforeAuthUrl();

    /**
     * Returns login url parameter for redirect
     * @return string|null
     * @since 2.0.0
     */
    public function getLoginUrl();

    /**
     * Returns action name which requires redirect
     * @return string|null
     * @since 2.0.0
     */
    public function getRedirectActionName();

    /**
     * Retrieve response object
     *
     * @return \Magento\Framework\App\ResponseInterface
     * @since 2.0.0
     */
    public function getResponse();
}
