<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Search\Adapter\Mysql\Field;

class Resolver implements ResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolve($fields)
    {
        return $fields;
    }
}
