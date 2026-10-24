<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Model\Query;

use Magento\Authorization\Model\UserContextInterface;

/**
 * Context factory
 */
interface ContextFactoryInterface
{
    /**
     * Create Context object
     *
     * @param UserContextInterface|null $userContext
     * @return ContextInterface
     */
    public function create(?UserContextInterface $userContext = null): ContextInterface;

    /**
     * Retrieve cached Context object
     *
     * @return ContextInterface
     */
    public function get(): ContextInterface;
}
