<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Helper\Product\Options\Loader;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Class ReadHandler
 */
class ReadHandler
{
    /**
     * @var Loader
     */
    private $optionLoader;

    /**
     * ReadHandler constructor
     *
     * @param Loader $optionLoader
     */
    public function __construct(Loader $optionLoader)
    {
        $this->optionLoader = $optionLoader;
    }

    /**
     * @param string $entityType
     * @param ProductInterface $entity
     * @return ProductInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entityType, $entity)
    {
        if ($entity->getTypeId() !== Configurable::TYPE_CODE) {
            return $entity;
        }

        $extensionAttributes = $entity->getExtensionAttributes();

        $extensionAttributes->setConfigurableProductLinks($this->getLinkedProducts($entity));
        $extensionAttributes->setConfigurableProductOptions($this->optionLoader->load($entity));

        $entity->setExtensionAttributes($extensionAttributes);

        return $entity;
    }

    /**
     * Get linked to configurable simple products
     *
     * @param ProductInterface $product
     * @return int[]
     */
    private function getLinkedProducts(ProductInterface $product)
    {
        /** @var Configurable $typeInstance */
        $typeInstance = $product->getTypeInstance();
        $childrenIds = $typeInstance->getChildrenIds($product->getId());

        if (isset($childrenIds[0])) {
            return $childrenIds[0];
        } else {
            return [];
        }
    }
}
