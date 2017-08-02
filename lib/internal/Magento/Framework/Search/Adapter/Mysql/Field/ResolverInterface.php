<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Field;

/**
 * Interface \Magento\Framework\Search\Adapter\Mysql\Field\ResolverInterface
 *
 * @since 2.0.0
 */
interface ResolverInterface
{
    /**
     * Resolve field
     *
     * @param array $fields
     * @return FieldInterface[]
     * @since 2.0.0
     */
    public function resolve(array $fields);
}
