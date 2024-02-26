<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained from
 * Adobe.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote;
use Magento\QuoteGraphQl\Model\CartItem\GetPaginatedCartItems;
use Magento\Framework\GraphQl\Query\Uid;

/**
 * @inheritdoc
 */
class CartItemsPaginated implements ResolverInterface
{
    private const SORT_ORDER_BY = 'item_id';

    private const SORT_ORDER = 'ASC';

    /**
     * @param GetPaginatedCartItems $pagination
     * @param Uid $uidEncoder
     */
    public function __construct(
        private readonly GetPaginatedCartItems $pagination,
        private readonly Uid $uidEncoder
    ) {
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var Quote $cart */
        $cart = $value['model'];
        $this->validate($args);

        $pageSize = $args['pageSize'];
        $currentPage = $args['currentPage'];
        $offset = ($currentPage - 1) * $pageSize;
        $order = CartItemsPaginated::SORT_ORDER;
        $orderBy = CartItemsPaginated::SORT_ORDER_BY;

        if (!empty($args['sort'])) {
            $order = $args['sort']['order'];
            $orderBy = mb_strtolower($args['sort']['field']);
        }

        $itemsData = [];
        try {
            $paginatedCartItems = $this->pagination->execute($cart, $pageSize, (int) $offset, $orderBy, $order);
            $cartProductsData = $this->getCartProductsData($paginatedCartItems['products']);

            foreach ($paginatedCartItems['items'] as $cartItem) {
                $productId = $cartItem->getProduct()->getId();
                if (!isset($cartProductsData[$productId])) {
                    $itemsData[] = new GraphQlNoSuchEntityException(
                        __("The product that was requested doesn't exist. Verify the product and try again.")
                    );
                    continue;
                }
                $cartItem->setQuote($cart);
                $itemsData[] = [
                    'id' => $cartItem->getItemId(),
                    'uid' => $this->uidEncoder->encode((string) $cartItem->getItemId()),
                    'quantity' => $cartItem->getQty(),
                    'product' => $cartProductsData[$productId],
                    'model' => $cartItem,
                ];
            }

            return [
                'items' => $itemsData,
                'total_count' => $paginatedCartItems['total'],
                'page_info' => [
                    'page_size' => $pageSize,
                    'current_page' => $currentPage,
                    'total_pages' => (int) ceil($paginatedCartItems['total'] / $pageSize)
                ],
            ];
        } catch (\Exception $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
    }

    /**
     * Validates arguments passed to resolver
     *
     * @param array $args
     * @throws GraphQlInputException
     */
    private function validate(array $args)
    {
        if (isset($args['currentPage']) && $args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }
        if (isset($args['pageSize']) && $args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }
    }

    /**
     * Get product data for cart items
     *
     * @param Product[] $products
     * @return array
     */
    private function getCartProductsData(array $products): array
    {
        $productsData = [];
        foreach ($products as $product) {
            $productsData[$product->getId()] = $product->getData();
            $productsData[$product->getId()]['model'] = $product;
            $productsData[$product->getId()]['uid'] = $this->uidEncoder->encode((string) $product->getId());
        }
        return $productsData;
    }
}
