<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Elasticsearch5\SearchAdapter\Aggregation;

/**
 * @deprecated Handle the Backward Compatibility issue with ES7 and ES8
 * @see AC-10652
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
class Interval extends \Magento\Elasticsearch\ElasticAdapter\SearchAdapter\Aggregation\Interval
{
}
