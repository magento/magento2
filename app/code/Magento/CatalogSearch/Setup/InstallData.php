<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Setup;


use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @param ProductAttributeRepositoryInterface $attributeRepository
     */
    public function __construct(ProductAttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->setWeight('sku', 6);
        $this->setWeight('name', 5);
    }

    /**
     * @param string $attributeCode
     * @param int $weight
     * @internal param AdapterInterface $connection
     */
    private function setWeight($attributeCode, $weight)
    {
        $attribute = $this->attributeRepository->get($attributeCode);
        $attribute->setSearchWeight($weight);
        $this->attributeRepository->save($attribute);
    }
}
