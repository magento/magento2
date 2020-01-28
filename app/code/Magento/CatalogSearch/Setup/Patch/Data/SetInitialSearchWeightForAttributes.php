<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Setup\Patch\Data;

use Magento\Framework\App\State;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Indexer\IndexerInterfaceFactory;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;

/**
 * @deprecated 101.0.0
 * @see \Magento\ElasticSearch
 */
class SetInitialSearchWeightForAttributes implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var IndexerInterfaceFactory
     */
    private $indexerFactory;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var State
     */
    private $state;

    /**
     * SetInitialSearchWeightForAttributes constructor.
     * @param IndexerInterfaceFactory $indexerFactory
     * @param ProductAttributeRepositoryInterface $attributeRepository
     * @param State $state
     */
    public function __construct(
        IndexerInterfaceFactory $indexerFactory,
        ProductAttributeRepositoryInterface $attributeRepository,
        State $state
    ) {
        $this->indexerFactory = $indexerFactory;
        $this->attributeRepository = $attributeRepository;
        $this->state = $state;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->setWeight('sku', 6);
        $this->setWeight('name', 5);
        $indexer = $this->indexerFactory->create()->load('catalogsearch_fulltext');
        $this->state->emulateAreaCode(
            \Magento\Framework\App\Area::AREA_CRONTAB,
            function () use ($indexer) {
                $indexer->reindexAll();
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Set attribute search weight.
     *
     * @param string $attributeCode
     * @param int $weight
     * @return void
     */
    private function setWeight($attributeCode, $weight)
    {
        $attribute = $this->attributeRepository->get($attributeCode);
        $attribute->setSearchWeight($weight);
        $this->attributeRepository->save($attribute);
    }
}
