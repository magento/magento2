<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Interception;

/**
 * Interface for any class that is intercepting another Magento class.
 *
 * This interface exposes the parent method of the interception class, which allows the caller to bypass
 * the interception logic.
 *
 * @api
 */
interface InterceptorInterface
{
    /**
     * Calls parent class method
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function ___callParent($method, array $arguments);
}
