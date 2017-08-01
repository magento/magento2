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
 * @since 2.0.0
 */
class ProductTypeList implements ProductTypeListInterface
{
    /**
     * Product type configuration provider
     *
     * @var ConfigInterface
     * @since 2.0.0
     */
    private $productTypeConfig;

    /**
     * Product type factory
     *
     * @var \Magento\Catalog\Api\Data\ProductTypeInterfaceFactory
     * @since 2.0.0
     */
    private $productTypeFactory;

    /**
     * List of product types
     *
     * @var array
     * @since 2.0.0
     */
    private $productTypes;

    /**
     * @param ConfigInterface $productTypeConfig
     * @param \Magento\Catalog\Api\Data\ProductTypeInterfaceFactory $productTypeFactory
     * @since 2.0.0
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
     * @since 2.0.0
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
