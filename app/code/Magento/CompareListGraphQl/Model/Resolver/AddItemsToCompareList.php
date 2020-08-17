<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Model\Resolver;

use Magento\Catalog\Model\CompareList as ModelCompareList;
use Magento\Catalog\Model\CompareListFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Compare\CompareList;
use Magento\Catalog\Model\Product\Compare\Item;
use Magento\Catalog\Model\Product\Compare\ItemFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\CompareList as ResourceCompareList;
use Magento\CompareListGraphQl\Model\Service\CompareListService;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Api\Data\StoreInterface;

class AddItemsToCompareList implements ResolverInterface
{
    /**
     * @var CompareListFactory
     */
    private $compareListFactory;

    /**
     * @var ResourceCompareList
     */
    private $resourceCompareList;

    /**
     * @var CompareListService
     */
    private $compareListService;

    /**
     * Compare item factory
     *
     * @var ItemFactory
     */
    private $compareItemFactory;

    /**
     * @var CompareList
     */
    private $compareList;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @param CompareListFactory $compareListFactory
     * @param ResourceCompareList $resourceCompareList
     * @param CompareListService $compareListService
     * @param ItemFactory $compareItemFactory
     * @param CompareList $compareList
     * @param ProductRepository $productRepository
     */
    public function __construct(
        CompareListFactory $compareListFactory,
        ResourceCompareList $resourceCompareList,
        CompareListService $compareListService,
        ItemFactory $compareItemFactory,
        CompareList $compareList,
        ProductRepository $productRepository
    ) {
        $this->compareListFactory = $compareListFactory;
        $this->resourceCompareList = $resourceCompareList;
        $this->compareListService = $compareListService;
        $this->compareItemFactory = $compareItemFactory;
        $this->compareList = $compareList;
        $this->productRepository = $productRepository;
    }

    /**
     * Add items to compare list
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     *
     * @return Value|mixed|void
     *
     * @throws GraphQlInputException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $listId = (int)$args['id'];
        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();
        /** @var  $compareListModel ModelCompareList*/
        $compareListModel = $this->compareListFactory->create();
        $this->resourceCompareList->load($compareListModel, $args['id']);

        if (!$compareListModel->getId()) {
            throw new GraphQlInputException(__('Can\'t load compare list.'));
        }

        foreach ($args['items'] as $key) {
            /* @var $item Item */
            $item = $this->compareItemFactory->create();
            $item->loadByProduct($key);
            if (!$item->getId() && $this->productExists($key)) {
                $item->addProductData($key);
                $item->setListId($listId);
                $item->save();
            }
        }

        return [
            'list_id' => $listId,
            'items' => $this->compareListService->getComparableItems($listId, $context, $store),
            'attributes' => $this->compareListService->getComparableAttributes($listId, $context)
        ];
    }

    /**
     * Check product exists.
     *
     * @param int|Product $product
     *
     * @return bool
     */
    private function productExists($product)
    {
        if ($product instanceof Product && $product->getId()) {
            return true;
        }

        try {
            $product = $this->productRepository->getById((int)$product);
            return !empty($product->getId());
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }
}
