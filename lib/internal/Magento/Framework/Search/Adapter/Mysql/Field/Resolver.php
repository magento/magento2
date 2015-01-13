<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
