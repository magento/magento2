<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Indexer\IndexerInterfaceFactory;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;

/**
 * Class \Magento\CatalogSearch\Setup\InstallData
 *
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var IndexerInterfaceFactory
     */
    private $indexerFactory;

    /**
     * @param IndexerInterfaceFactory $indexerFactory
     * @param ProductAttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        IndexerInterfaceFactory $indexerFactory,
        ProductAttributeRepositoryInterface $attributeRepository
    ) {
        $this->indexerFactory = $indexerFactory;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Installs data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->setWeight('sku', 6);
        $this->setWeight('name', 5);
        $this->getIndexer('catalogsearch_fulltext')->reindexAll();
    }

    /**
     * @param string $indexerId
     * @return \Magento\Framework\Indexer\IndexerInterface
     */
    private function getIndexer($indexerId)
    {
        return $this->indexerFactory->create()->load($indexerId);
    }

    /**
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
