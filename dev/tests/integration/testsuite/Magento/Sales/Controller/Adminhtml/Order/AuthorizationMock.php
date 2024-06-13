<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

class AuthorizationMock extends \Magento\Framework\Authorization
{
    /**
     * Check current user permission on resource and privilege
     *
     * @param   string $resource
     * @param   string $privilege
     * @return  boolean
     */
    public function isAllowed($resource, $privilege = null)
    {
        return $resource == 'Magento_Sales::create' ? false : parent::isAllowed($resource, $privilege);
    }
}
