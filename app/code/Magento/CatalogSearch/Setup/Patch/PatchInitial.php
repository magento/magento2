<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Setup\Patch;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Indexer\IndexerInterfaceFactory;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class PatchInitial implements \Magento\Setup\Model\Patch\DataPatchInterface
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
    public function apply(ModuleDataSetupInterface $setup)
    {
        $this->setWeight('sku', 6);
        $this->setWeight('name', 5);
        $this->getIndexer('catalogsearch_fulltext')->reindexAll();

    }

    /**
     * Do Revert
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function revert(ModuleDataSetupInterface $setup)
    {
    }

    /**
     * @inheritdoc
     */
    public function isDisabled()
    {
        return false;
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
