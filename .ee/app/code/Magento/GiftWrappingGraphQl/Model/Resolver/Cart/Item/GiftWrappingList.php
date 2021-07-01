<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GiftWrappingGraphQl\Model\Resolver\Cart\Item;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GiftWrapping\Api\Data\WrappingInterface;
use Magento\GiftWrapping\Api\WrappingRepositoryInterface;
use Magento\GiftWrapping\Helper\Data as GiftWrappingHelper;
use Magento\NegotiableQuote\Model\PriceCurrency;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;

/**
 * Class gets data about available gift wrappings for cart items
 */
class GiftWrappingList implements ResolverInterface
{
    private const ENABLE_STATUS = 1;

    /**
     * @var WrappingRepositoryInterface
     */
    private $wrappingRepository;

    /**
     * @var GiftWrappingHelper
     */
    private $giftWrappingData;

    /**
     * @var SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * @param WrappingRepositoryInterface $wrappingRepository
     * @param SearchCriteriaBuilder $criteriaBuilder
     * @param GiftWrappingHelper $giftWrappingData
     * @param PriceCurrency $priceCurrency
     */
    public function __construct(
        WrappingRepositoryInterface $wrappingRepository,
        SearchCriteriaBuilder $criteriaBuilder,
        GiftWrappingHelper $giftWrappingData,
        PriceCurrency $priceCurrency
    ) {
        $this->wrappingRepository = $wrappingRepository;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->giftWrappingData = $giftWrappingData;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Get data about available gift wrappings for cart item
     *
     * @param Field            $field
     * @param ContextInterface $context
     * @param ResolveInfo      $info
     * @param array|null       $value
     * @param array|null       $args
     *
     * @return array|Value|mixed|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!$this->giftWrappingData->isGiftWrappingAvailableForItems()) {
            return [];
        }

        if (isset($value['product']['gift_wrapping_available'])) {
            if (empty($value['product']['gift_wrapping_available'])) {
                return [];
            }
        }

        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();
        $giftWrappingsList = $this->getGiftWrappingsList($store);

        if (empty($giftWrappingsList)) {
            return [];
        }

        $availableGiftWrappings = [];
        foreach ($giftWrappingsList as $item) {
            $availableGiftWrappings[] = $this->getGiftWrappingItemData($item, $store);
        }

        return $availableGiftWrappings;
    }

    /**
     * Get available gift wrappings for cart item
     *
     * @param StoreInterface $store
     *
     * @return WrappingInterface[]
     */
    private function getGiftWrappingsList(StoreInterface $store): array
    {
        return $this->wrappingRepository->getList(
            $this->criteriaBuilder
                ->addFilter(WrappingInterface::STATUS, self::ENABLE_STATUS)
                ->addFilter(Store::STORE_ID, $store->getStoreId())
                ->addFilter(WrappingInterface::WEBSITE_IDS, [$store->getWebsiteId()], 'in')
                ->create()
        )->getItems();
    }

    /**
     * Get data for gift wrapping item
     *
     * @param WrappingInterface       $item
     * @param StoreInterface          $store
     *
     * @return array
     */
    private function getGiftWrappingItemData(WrappingInterface $item, StoreInterface $store): array
    {
        return [
            'id' => $item->getWrappingId() ?? '',
            'design' => $item->getDesign() ?? '',
            'price' => [
                'value' => $item->getBasePrice() ?? '',
                'currency' => $store->getCurrentCurrencyCode(),
                'formatted' => $this->priceCurrency->format($item->getBasePrice() ?? '',false,null,null,$store->getCurrentCurrencyCode())
            ],
            'image' => [
                'label'=> $item->getImage() ?? '',
                'url'=> $item->getImageUrl() ?? ''
            ]
        ];
    }
}
