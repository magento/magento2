<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Setup\Patch;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Indexer\IndexerInterfaceFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class PatchInitial
{


    /**
     * @param IndexerInterfaceFactory $indexerFactory
     */
    private $indexerFactory;
    /**
     * @param ProductAttributeRepositoryInterface $attributeRepository
     */
    private $attributeRepository;

    /**
     * @param IndexerInterfaceFactory $indexerFactory @param ProductAttributeRepositoryInterface $attributeRepository
     */
    public function __construct(IndexerInterfaceFactory $indexerFactory,
                                ProductAttributeRepositoryInterface $attributeRepository)
    {
        $this->indexerFactory = $indexerFactory;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Do Upgrade
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function up(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->setWeight('sku', 6);
        $this->setWeight('name', 5);
        $this->getIndexer('catalogsearch_fulltext')->reindexAll();

    }

    private function setWeight($attributeCode, $weight
    )
    {
        $attribute = $this->attributeRepository->get($attributeCode);
        $attribute->setSearchWeight($weight);
        $this->attributeRepository->save($attribute);

    }

    private function getIndexer($indexerId
    )
    {
        return $this->indexerFactory->create()->load($indexerId);

    }
}
