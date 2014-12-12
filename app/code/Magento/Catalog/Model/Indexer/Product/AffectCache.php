<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Model\Indexer\Product;

/**
 * Class AffectCache
 */
class AffectCache
{
    /**
     * @var \Magento\Indexer\Model\CacheContext $context
     */
    protected $context;

    /**
     * @param \Magento\Indexer\Model\CacheContext $context
     */
    public function __construct(
        \Magento\Indexer\Model\CacheContext $context
    ) {
        $this->context = $context;
    }

    /**
     * @param \Magento\Indexer\Model\ActionInterface $subject
     * @param array $ids
     * @return array
     */
    public function beforeExecute(\Magento\Indexer\Model\ActionInterface $subject, $ids)
    {
        $this->context->registerEntities(\Magento\Catalog\Model\Product::CACHE_TAG, $ids);
        return [$ids];
    }
}
