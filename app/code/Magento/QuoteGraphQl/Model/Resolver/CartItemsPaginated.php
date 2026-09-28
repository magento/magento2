<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote;
use Magento\QuoteGraphQl\Model\CartItem\GetItemsData;
use Magento\QuoteGraphQl\Model\CartItem\GetPaginatedCartItems;

/**
 * @inheritdoc
 */
class CartItemsPaginated implements ResolverInterface
{
    private const SORT_ORDER_BY = 'item_id';
    private const SORT_ORDER = 'ASC';

    /**
     * @param GetPaginatedCartItems $pagination
     * @param GetItemsData $getItemsData
     */
    public function __construct(
        private readonly GetPaginatedCartItems $pagination,
        private readonly GetItemsData $getItemsData
    ) {
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var Quote $cart */
        $cart = $value['model'];
        $this->validate($args);

        $pageSize = $args['pageSize'];
        $currentPage = $args['currentPage'];
        $order = CartItemsPaginated::SORT_ORDER;
        $orderBy = CartItemsPaginated::SORT_ORDER_BY;

        if (!empty($args['sort'])) {
            $order = $args['sort']['order'];
            $orderBy = mb_strtolower($args['sort']['field']);
        }

        $allVisibleItems = $cart->getAllVisibleItems();
        $paginatedCartItems = $this->pagination->execute($cart, $pageSize, (int) $currentPage, $orderBy, $order);

        $cartItems = [];
        /** @var CartItemInterface $cartItem */
        foreach ($paginatedCartItems['items'] as $cartItem) {
            foreach ($allVisibleItems as $item) {
                if ($cartItem->getId() == $item->getId()) {
                    $cartItems[] = $item;
                }
            }
        }
        $itemsData = $this->getItemsData->execute($cartItems);

        return [
            'items' => $itemsData,
            'total_count' => $paginatedCartItems['total'],
            'page_info' => [
                'page_size' => $pageSize,
                'current_page' => $currentPage,
                'total_pages' => (int) ceil($paginatedCartItems['total'] / $pageSize)
            ],
        ];
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
}
