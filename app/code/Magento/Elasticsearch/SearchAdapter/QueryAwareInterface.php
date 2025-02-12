<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\SearchAdapter;

/**
 * QueryAwareInterface is a marker interface for those objects who expects the QueryContainer
 * to be passed to their constructor
 *
 * Its goal is to mark the fact that a class which implements this interface requires
 * the QueryContainer object to be passed as constructor argument
 * with the name 'queryContainer' to work properly
 *
 * @api
 */
interface QueryAwareInterface
{
}
