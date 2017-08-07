<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Category\Product\Plugin;

use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Catalog\Model\Indexer\Category\Product;

/**
 * Class \Magento\Catalog\Model\Indexer\Category\Product\Plugin\StoreGroup
 *
 */
class StoreGroup
{
    /**
     * @var bool
     * @since 2.2.0
     */
    private $needInvalidating;

    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(IndexerRegistry $indexerRegistry)
    {
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * Check if need invalidate flat category indexer
     *
     * @param AbstractDb $subject
     * @param AbstractModel $group
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function beforeSave(AbstractDb $subject, AbstractModel $group)
    {
        $this->needInvalidating = $this->validate($group);
    }

    /**
     * Invalidate flat product
     *
     * @param AbstractDb $subject
     * @param AbstractDb $objectResource
     *
     * @return AbstractDb
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function afterSave(AbstractDb $subject, AbstractDb $objectResource)
    {
        if ($this->needInvalidating) {
            $this->indexerRegistry->get(Product::INDEXER_ID)->invalidate();
        }

        return $objectResource;
    }

    /**
     * Validate changes for invalidating indexer
     *
     * @param AbstractModel $group
     * @return bool
     */
    protected function validate(AbstractModel $group)
    {
        return ($group->dataHasChangedFor('website_id') || $group->dataHasChangedFor('root_category_id'))
               && !$group->isObjectNew();
    }
}
