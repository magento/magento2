<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\App;

/**
 * Backend Application which uses Magento Backend authentication process
 */
class BackendApp
{
    /**
     * @var null|string
     */
    private $cookiePath = null;

    /**
     * @var null|string
     */
    private $startupPage = null;

    /**
     * @var null|string
     */
    private $aclResourceName = null;

    /**
     * @param string $cookiePath
     * @param string $startupPage
     * @param string $aclResourceName
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
     * @return null|string
     */
    public function getCookiePath()
    {
        return $this->cookiePath;
    }

    /**
     * Startup Page of the application to redirect after login
     *
     * @return null|string
     */
    public function getStartupPage()
    {
        return $this->startupPage;
    }

    /**
     * ACL resource name to authorize access to
     *
     * @return null|string
     */
    public function getAclResource()
    {
        return $this->aclResourceName;
    }
}