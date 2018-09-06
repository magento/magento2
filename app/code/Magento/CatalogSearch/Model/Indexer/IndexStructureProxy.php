<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer;

use Magento\Framework\Indexer\IndexStructureInterface;

/**
 * @deprecated CatalogSearch will be removed in 2.4, and {@see \Magento\ElasticSearch}
 *             will replace it as the default search engine.
 */
class IndexStructureProxy implements IndexStructureInterface
{
    /**
     * @var IndexStructureInterface
     */
    private $indexStructureEntity;

    /**
     * @var IndexStructureFactory
     */
    private $indexStructureFactory;

    /**
     * @param IndexStructureFactory $indexStructureFactory
     */
    public function __construct(
        IndexStructureFactory $indexStructureFactory
    ) {
        $this->indexStructureFactory = $indexStructureFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        $index,
        array $dimensions = []
    ) {
        return $this->getEntity()->delete($index, $dimensions);
    }

    /**
     * {@inheritdoc}
     */
    public function create(
        $index,
        array $fields,
        array $dimensions = []
    ) {
        return $this->getEntity()->create($index, $fields, $dimensions);
    }

    /**
     * Get instance of current index structure
     *
     * @return IndexStructureInterface
     */
    private function getEntity()
    {
        if (empty($this->indexStructureEntity)) {
            $this->indexStructureEntity = $this->indexStructureFactory->create();
        }
        return $this->indexStructureEntity;
    }
}
