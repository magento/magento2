<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Helper\Product\Options\Loader;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ReadHandler
 */
class ReadHandler implements ExtensionInterface
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
     * @param object $entity
     * @param array $arguments
     * @return object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
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
