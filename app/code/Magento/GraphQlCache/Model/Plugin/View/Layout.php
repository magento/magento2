<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Model\Plugin\View;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\GraphQlCache\Model\CacheableQuery;

class Layout
{
    /**
     * @param CacheableQuery $cacheableQuery
     */
    public function __construct(
        private readonly CacheableQuery $cacheableQuery
    ) {
    }

    /**
     * Add block cache tags to cacheable query
     *
     * @param LayoutInterface $subject
     * @param mixed $result
     * @param mixed $name
     * @param mixed $block
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSetBlock(
        LayoutInterface $subject,
        mixed $result,
        mixed $name,
        mixed $block
    ): mixed {
        if ($block instanceof IdentityInterface) {
            $this->cacheableQuery->addCacheTags($block->getIdentities());
        }
        return $result;
    }
}
