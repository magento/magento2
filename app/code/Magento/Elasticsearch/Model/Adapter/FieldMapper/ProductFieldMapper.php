<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\FieldMapper;

use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\Elasticsearch5\Model\Adapter\FieldMapper\ProductFieldMapper
    as Elasticsearch5ProductFieldMapper;

/**
 * Class ProductFieldMapper
 */
class ProductFieldMapper extends Elasticsearch5ProductFieldMapper implements FieldMapperInterface
{
}
