<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Indexer\Category;

/**
 * Class AffectCache
 * @deprecated
 */
class AffectCache
{
    /**
     * @var \Magento\Framework\Indexer\CacheContext
     */
    protected $context;

    /**
     * @param \Magento\Framework\Indexer\CacheContext $context
     */
    public function __construct(
        \Magento\Framework\Indexer\CacheContext $context
    ) {
        $this->context = $context;
    }

    /**
     * @param \Magento\Framework\Indexer\ActionInterface $subject
     * @param array $ids
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(\Magento\Framework\Indexer\ActionInterface $subject, $ids)
    {
        $this->context->registerEntities(\Magento\Catalog\Model\Category::CACHE_TAG, $ids);
        return [$ids];
    }
}
