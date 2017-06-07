<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\App;

/**
 * Backend Application which uses Magento Backend authentication process
 */
class BackendApp
{
    /**
     * @var null
     */
    private $cookiePath;

    /**
     * @var null
     */
    private $startupPage;

    /**
     * @var null
     */
    private $aclResourceName;

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
     * @return string
     */
    public function getCookiePath()
    {
        return $this->cookiePath;
    }

    /**
     * Startup Page of the application to redirect after login
     *
     * @return string
     */
    public function getStartupPage()
    {
        return $this->startupPage;
    }

    /**
     * ACL resource name to authorize access to
     *
     * @return string
     */
    public function getAclResource()
    {
        return $this->aclResourceName;
    }
}
