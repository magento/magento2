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
<<<<<<< HEAD
    /**
     * @var string[]
     */
    private $itemResolvers = [];

    /**
     * @var ItemResolverInterface[]
     */
=======
    /** @var string[] */
    private $itemResolvers = [];

    /** @var ItemResolverInterface[] */
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
    public function getFinalProduct(ItemInterface $item): ProductInterface
    {
        $finalProduct = $item->getProduct();

=======
    public function getFinalProduct(ItemInterface $item) : ProductInterface
    {
        $finalProduct = $item->getProduct();
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        foreach ($this->itemResolvers as $resolver) {
            $resolvedProduct = $this->getItemResolverInstance($resolver)->getFinalProduct($item);
            if ($resolvedProduct !== $finalProduct) {
                $finalProduct = $resolvedProduct;
                break;
            }
        }
<<<<<<< HEAD

=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        return $finalProduct;
    }

    /**
     * Get the instance of the item resolver by class name.
     *
     * @param string $className
     * @return ItemResolverInterface
     */
<<<<<<< HEAD
    private function getItemResolverInstance(string $className): ItemResolverInterface
=======
    private function getItemResolverInstance(string $className) : ItemResolverInterface
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        if (!isset($this->itemResolversInstances[$className])) {
            $this->itemResolversInstances[$className] = ObjectManager::getInstance()->get($className);
        }
<<<<<<< HEAD

=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        return $this->itemResolversInstances[$className];
    }
}
