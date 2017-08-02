<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\App;

/**
 * Backend Application which uses Magento Backend authentication process
 * @api
 * @since 2.0.0
 */
class BackendApp
{
    /**
     * @var null
     * @since 2.0.0
     */
    private $cookiePath;

    /**
     * @var null
     * @since 2.0.0
     */
    private $startupPage;

    /**
     * @var null
     * @since 2.0.0
     */
    private $aclResourceName;

    /**
     * @param string $cookiePath
     * @param string $startupPage
     * @param string $aclResourceName
     * @since 2.0.0
     */
    public function __construct(
        $cookiePath,
        $startupPage,
        $aclResourceName
    ) {
        $this->cookiePath = $cookiePath;
        $this->startupPage = $startupPage;
        $this->aclResourceName = $aclResourceName;
    }

    /**
     * Cookie path for the application to set cookie to
     *
     * @return string
     * @since 2.0.0
     */
    public function getCookiePath()
    {
        return $this->cookiePath;
    }

    /**
     * Startup Page of the application to redirect after login
     *
     * @return string
     * @since 2.0.0
     */
    public function getStartupPage()
    {
        return $this->startupPage;
    }

    /**
     * ACL resource name to authorize access to
     *
     * @return string
     * @since 2.0.0
     */
    public function getAclResource()
    {
        return $this->aclResourceName;
    }
}
