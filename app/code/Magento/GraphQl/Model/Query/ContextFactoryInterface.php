<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Model\Query;

/**
 * Context factory
 */
interface ContextFactoryInterface
{
    /**
     * Create Context object
     *
     * @return ContextInterface
     */
    public function create(): ContextInterface;
}
