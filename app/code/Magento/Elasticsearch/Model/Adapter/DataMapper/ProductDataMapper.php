<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\DataMapper;

use Magento\Elasticsearch\Model\Adapter\DataMapperInterface;
use Magento\Elasticsearch\Elasticsearch5\Model\Adapter\DataMapper\ProductDataMapper as ElasticSearch5ProductDataMapper;

/**
 * Don't use this product data mapper class.
 *
 * @deprecated 100.2.0
 * @see \Magento\Elasticsearch\Model\Adapter\BatchDataMapperInterface
 */
class ProductDataMapper extends ElasticSearch5ProductDataMapper implements DataMapperInterface
{
}
