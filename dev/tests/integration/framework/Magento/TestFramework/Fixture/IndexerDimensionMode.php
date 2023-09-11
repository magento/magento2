<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class IndexerDimensionMode
{
    /**
     * @param string $indexer
     * @param string $dimension
     */
    public function __construct(
        public string $indexer,
        public string $dimension
    ) {
    }
}
