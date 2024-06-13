<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Category;

/**
 * Mock for authorization process
 */
class AuthorizationMock extends \Magento\Framework\Authorization
{
    /**
     * @inheritdoc
     */
    public function isAllowed($resource, $privilege = null)
    {
        return false;
    }
}
