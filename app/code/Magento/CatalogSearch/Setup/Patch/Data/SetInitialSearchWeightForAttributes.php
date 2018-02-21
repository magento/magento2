<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Setup\Patch\Data;

use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;
use Magento\Framework\Indexer\IndexerInterfaceFactory;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;

/**
 * Class SetInitialSearchWeightForAttributes
 * @package Magento\CatalogSearch\Setup\Patch
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
     * SetInitialSearchWeightForAttributes constructor.
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
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->setWeight('sku', 6);
        $this->setWeight('name', 5);
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
     * @param $attributeCode
     * @param $weight
     */
    private function setWeight($attributeCode, $weight)
    {
        $attribute = $this->attributeRepository->get($attributeCode);
        $attribute->setSearchWeight($weight);
        $this->attributeRepository->save($attribute);
    }
}
