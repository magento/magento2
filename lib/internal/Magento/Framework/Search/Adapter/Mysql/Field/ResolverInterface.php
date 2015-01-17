<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Field;

interface ResolverInterface
{
    /**
     * Resolve field
     *
     * @param string|array $fields
     * @return string|array
     */
    public function resolve($fields);
}
