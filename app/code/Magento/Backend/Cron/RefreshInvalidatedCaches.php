<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Cron;

use Magento\Framework\App\Cache\TypeListInterface;

class RefreshInvalidatedCaches
{
    /** @var TypeListInterface */
    private TypeListInterface $typeList;

    /**
     * @param TypeListInterface $typeList
     */
    public function __construct(
        TypeListInterface $typeList
    ) {
        $this->typeList = $typeList;
    }

    /**
     * Entry point for cronjob 'backend_refresh_invalidated_caches'
     */
    public function execute(): void
    {
        foreach ($this->typeList->getInvalidated() as $cache) {
            $this->typeList->cleanType($cache->getId());
        }
    }
}
