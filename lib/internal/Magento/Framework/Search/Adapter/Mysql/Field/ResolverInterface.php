<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
