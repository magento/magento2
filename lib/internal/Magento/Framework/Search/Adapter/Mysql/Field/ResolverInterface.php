<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Field;

/**
 * MySQL search field resolver.
 *
 * @deprecated
 * @see \Magento\ElasticSearch
 */
interface ResolverInterface
{
    /**
     * Resolve field.
     *
     * @param array $fields
     * @return FieldInterface[]
     */
    public function resolve(array $fields);
}
