<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer;

use Magento\Framework\Indexer\IndexStructureInterface;

/**
 * Class \Magento\CatalogSearch\Model\Indexer\IndexStructureProxy
 *
 * @since 2.1.0
 */
class IndexStructureProxy implements IndexStructureInterface
{
    /**
     * @var IndexStructureInterface
     * @since 2.1.0
     */
    private $indexStructureEntity;

    /**
     * @var IndexStructureFactory
     * @since 2.1.0
     */
    private $indexStructureFactory;

    /**
     * @param IndexStructureFactory $indexStructureFactory
     * @since 2.1.0
     */
    public function __construct(
        IndexStructureFactory $indexStructureFactory
    ) {
        $this->indexStructureFactory = $indexStructureFactory;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function delete(
        $index,
        array $dimensions = []
    ) {
        return $this->getEntity()->delete($index, $dimensions);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
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
     * @since 2.1.0
     */
    private function getEntity()
    {
        if (empty($this->indexStructureEntity)) {
            $this->indexStructureEntity = $this->indexStructureFactory->create();
        }
        return $this->indexStructureEntity;
    }
}
