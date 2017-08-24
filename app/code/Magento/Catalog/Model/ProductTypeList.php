<?php
/**
 * Product type provider
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model;

use Magento\Catalog\Api\ProductTypeListInterface;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;

/**
 * Class \Magento\Catalog\Model\ProductTypeList
 *
 */
class ProductTypeList implements ProductTypeListInterface
{
    /**
     * Product type configuration provider
     *
     * @var ConfigInterface
     */
    private $productTypeConfig;

    /**
     * Product type factory
     *
     * @var \Magento\Catalog\Api\Data\ProductTypeInterfaceFactory
     */
    private $productTypeFactory;

    /**
     * List of product types
     *
     * @var array
     */
    private $productTypes;

    /**
     * @param ConfigInterface $productTypeConfig
     * @param \Magento\Catalog\Api\Data\ProductTypeInterfaceFactory $productTypeFactory
     */
    public function __construct(
        ConfigInterface $productTypeConfig,
        \Magento\Catalog\Api\Data\ProductTypeInterfaceFactory $productTypeFactory
    ) {
        $this->productTypeConfig = $productTypeConfig;
        $this->productTypeFactory = $productTypeFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductTypes()
    {
        if ($this->productTypes === null) {
            $productTypes = [];
            foreach ($this->productTypeConfig->getAll() as $productTypeData) {
                /** @var \Magento\Catalog\Api\Data\ProductTypeInterface $productType */
                $productType = $this->productTypeFactory->create();
                $productType->setName($productTypeData['name'])
                    ->setLabel($productTypeData['label']);
                $productTypes[] = $productType;
            }
            $this->productTypes = $productTypes;
        }
        return $this->productTypes;
    }
}
