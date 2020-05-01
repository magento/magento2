<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GiftMessageGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GiftMessage\Api\ItemRepositoryInterface;

/**
 * Class provides ability to get GiftMessage for cart item
 */
class GiftMessageItem implements ResolverInterface
{
    /**
     * @var ItemRepositoryInterface
     */
    private $itemRepository;

    /**
     * GiftMessageItem constructor.
     *
     * @param ItemRepositoryInterface $itemRepository
     */
    public function __construct(
        ItemRepositoryInterface $itemRepository
    ) {
        $this->itemRepository = $itemRepository;
    }

    /**
     * Return information about Gift message for item cart
     *
     * @param Field            $field
     * @param ContextInterface $context
     * @param ResolveInfo      $info
     * @param array|null       $value
     * @param array|null       $args
     *
     * @return array|Value|mixed
     * @throws GraphQlInputException
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new GraphQlInputException(__('"model" value should be specified'));
        }

        $quoteItem = $value['model'];

        $giftItemMessage = $this->itemRepository->get($quoteItem->getQuoteId(), $quoteItem->getItemId());

        return [
            'to' => $giftItemMessage !== null ? $giftItemMessage->getRecipient() : '',
            'from' => $giftItemMessage !== null ? $giftItemMessage->getSender() : '',
            'message'=> $giftItemMessage !== null ? $giftItemMessage->getMessage() : ''
        ];
    }
}
