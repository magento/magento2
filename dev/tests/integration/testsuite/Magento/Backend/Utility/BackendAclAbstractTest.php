<?php
/***
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Utility;

/**
 * Contains logic for testing authorization mechanism of controllers
 */
class BackendAclAbstractTest extends \Magento\Backend\Utility\Controller
{
    /**
     * The resource used to authorize action
     *
     * @var string
     */
    protected $resource;

    /**
     * The uri at which to access the controller
     *
     * @var string
     */
    protected $uri;

    public function testAclHasAccess()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Framework\Acl\Builder')
            ->getAcl()
            ->allow(null, $this->resource);;
        $this->dispatch($this->uri);
        $this->assertNotSame(403, $this->getResponse()->getHttpResponseCode());
    }

    public function testAclNoAccess()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Framework\Acl\Builder')
            ->getAcl()
            ->deny(null, $this->resource);;
        $this->dispatch($this->uri);
        $this->assertSame(403, $this->getResponse()->getHttpResponseCode());
    }
}
