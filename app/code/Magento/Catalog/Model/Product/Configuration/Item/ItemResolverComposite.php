<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Configuration\Item;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ObjectManager;

/**
 * {@inheritdoc}
 */
class ItemResolverComposite implements ItemResolverInterface
{
    /**
     * @var string[]
     */
    private $itemResolvers = [];

    /**
     * @var ItemResolverInterface[]
     */
    private $itemResolversInstances = [];

    /**
     * @param string[] $itemResolvers
     */
    public function __construct(array $itemResolvers)
    {
        $this->itemResolvers = $itemResolvers;
    }

    /**
     * {@inheritdoc}
     */
    public function getFinalProduct(ItemInterface $item): ProductInterface
    {
        $finalProduct = $item->getProduct();

        foreach ($this->itemResolvers as $resolver) {
            $resolvedProduct = $this->getItemResolverInstance($resolver)->getFinalProduct($item);
            if ($resolvedProduct !== $finalProduct) {
                $finalProduct = $resolvedProduct;
                break;
            }
        }

        return $finalProduct;
    }

    /**
     * Get the instance of the item resolver by class name.
     *
     * @param string $className
     * @return ItemResolverInterface
     */
    private function getItemResolverInstance(string $className): ItemResolverInterface
    {
        if (!isset($this->itemResolversInstances[$className])) {
            $this->itemResolversInstances[$className] = ObjectManager::getInstance()->get($className);
        }

        return $this->itemResolversInstances[$className];
    }
}
