<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Adapter\Mysql\Field;

use Magento\Framework\Search\Adapter\Mysql\Field\ResolverInterface;

class Resolver implements ResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolve($fields)
    {
        return 'data_index';
    }
}
