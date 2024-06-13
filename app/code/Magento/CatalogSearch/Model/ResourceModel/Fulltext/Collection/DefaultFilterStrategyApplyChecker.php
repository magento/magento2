<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

namespace Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;

/**
 * This class add in backward compatibility purposes to check if need to apply old strategy for filter prepare process.
 * @deprecated 101.0.2
 */
class DefaultFilterStrategyApplyChecker implements DefaultFilterStrategyApplyCheckerInterface
{
    /**
     * Check if this strategy applicable for current engine.
     *
     * @return bool
     */
    public function isApplicable(): bool
    {
        return true;
    }
}
