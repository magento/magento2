<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
