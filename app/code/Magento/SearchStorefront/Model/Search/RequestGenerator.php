<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SearchStorefront\Model\Search;

use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Catalog\Model\Entity\Attribute;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\SearchStorefront\Model\Search\RequestGenerator\GeneratorResolver;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Search\Request\QueryInterface;

/**
 * Catalog search request generator.
 *
 * @api
 * @since 100.0.2
 */
class RequestGenerator
{
    /** Filter name suffix */
    const FILTER_SUFFIX = '_filter';

    /** Bucket name suffix */
    const BUCKET_SUFFIX = '_bucket';
}
