<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Model\Indexer\Rule;

use Magento\CatalogRule\Model\Indexer\AbstractIndexer;

/**
 * Class \Magento\CatalogRule\Model\Indexer\Rule\RuleProductIndexer
 *
 * @since 2.0.0
 */
class RuleProductIndexer extends AbstractIndexer
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    protected function doExecuteList($ids)
    {
        $this->indexBuilder->reindexFull();
        $this->getCacheContext()->registerTags($this->getIdentities());
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    protected function doExecuteRow($id)
    {
        $this->indexBuilder->reindexFull();
    }
}
