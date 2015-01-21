<?php
/**
 * Product type provider
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model;

use Magento\Catalog\Api\ProductTypeListInterface;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;

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
     * @var \Magento\Catalog\Api\Data\ProductTypeDataBuilder
     */
    private $productTypeBuilder;

    /**
     * List of product types
     *
     * @var array
     */
    private $productTypes;

    /**
     * @param ConfigInterface $productTypeConfig
     * @param \Magento\Catalog\Api\Data\ProductTypeDataBuilder $productTypeBuilder
     */
    public function __construct(
        ConfigInterface $productTypeConfig,
        \Magento\Catalog\Api\Data\ProductTypeDataBuilder $productTypeBuilder
    ) {
        $this->productTypeConfig = $productTypeConfig;
        $this->productTypeBuilder = $productTypeBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductTypes()
    {
        if (is_null($this->productTypes)) {
            $productTypes = [];
            foreach ($this->productTypeConfig->getAll() as $productTypeData) {
                $productTypes[] = $this->productTypeBuilder->populateWithArray(
                    [
                        'name' => $productTypeData['name'],
                        'label' => $productTypeData['label'],
                    ]
                )->create();
            }
            $this->productTypes = $productTypes;
        }
        return $this->productTypes;
    }
}
