<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Model\Plugin;

use Magento\Catalog\Model\ResourceModel\Category as Resource;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Processor;

/**
 * Perform indexer invalidation after a category delete.
 */
class Category
{
    /**
     * @var Processor
     */
    private $fulltextIndexerProcessor;

    /**
     * @param Processor $fulltextIndexerProcessor
     */
    public function __construct(Processor $fulltextIndexerProcessor)
    {
        $this->fulltextIndexerProcessor = $fulltextIndexerProcessor;
    }

    /**
     * Mark fulltext indexer as invalid post-deletion of category.
     *
     * @param Resource $subjectCategory
     * @param Resource $resultCategory
     * @return Resource
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(Resource $subjectCategory, Resource $resultCategory) : Resource
    {
        $this->fulltextIndexerProcessor->markIndexerAsInvalid();

        return $resultCategory;
    }
}
