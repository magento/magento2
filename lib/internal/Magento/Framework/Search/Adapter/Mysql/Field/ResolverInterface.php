<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Field;

interface ResolverInterface
{
    /**
     * Resolve field
     *
     * @param array $fields
     * @return FieldInterface[]
     */
    public function resolve(array $fields);
}
