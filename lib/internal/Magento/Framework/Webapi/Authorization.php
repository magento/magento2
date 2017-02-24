<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi;

/**
 * Web API authorization model.
 */
class Authorization
{
    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\AuthorizationInterface $authorization
     */
    public function __construct(\Magento\Framework\AuthorizationInterface $authorization)
    {
        $this->authorization = $authorization;
    }

    /**
     * Check if all ACL resources are allowed to be accessed by current API user.
     *
     * @param string[] $aclResources
     * @return bool
     */
    public function isAllowed($aclResources)
    {
        foreach ($aclResources as $resource) {
            if (!$this->authorization->isAllowed($resource)) {
                return false;
            }
        }
        return true;
    }
}
