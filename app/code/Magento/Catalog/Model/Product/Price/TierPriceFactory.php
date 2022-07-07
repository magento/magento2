<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Price;

use Magento\Catalog\Api\Data\TierPriceInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class TierPriceFactory
{
    /**
     * @var \Magento\Catalog\Api\Data\TierPriceInterfaceFactory
     */
    private $tierPriceFactory;

    /**
     * @var TierPricePersistence
     */
    private $tierPricePersistence;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    private $customerGroupRepository;

    /**
     * @var string
     */
    private $allGroupsValue = 'all groups';

    /**
     * @var int
     */
    private $allGroupsId = 1;

    /**
     * @var array
     */
    private $customerGroupsByCode = [];

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;

    /**
     * TierPriceBuilder constructor.
     *
     * @param \Magento\Catalog\Api\Data\TierPriceInterfaceFactory $tierPriceFactory
     * @param TierPricePersistence $tierPricePersistence
     * @param \Magento\Customer\Api\GroupRepositoryInterface $customerGroupRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     */
    public function __construct(
        \Magento\Catalog\Api\Data\TierPriceInterfaceFactory $tierPriceFactory,
        TierPricePersistence $tierPricePersistence,
        \Magento\Customer\Api\GroupRepositoryInterface $customerGroupRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder
    ) {
        $this->tierPriceFactory = $tierPriceFactory;
        $this->tierPricePersistence = $tierPricePersistence;
        $this->customerGroupRepository = $customerGroupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * Create populated tier price DTO.
     *
     * @param array $rawPrice
     * @param string $sku
     * @return \Magento\Catalog\Api\Data\TierPriceInterface
     */
    public function create(array $rawPrice, $sku)
    {
        $price = $this->tierPriceFactory->create();
        $price->setPrice(isset($rawPrice['percentage_value']) ? $rawPrice['percentage_value'] : $rawPrice['value']);
        $price->setPriceType(
            isset($rawPrice['percentage_value'])
            ? TierPriceInterface::PRICE_TYPE_DISCOUNT
            : TierPriceInterface::PRICE_TYPE_FIXED
        );
        $price->setWebsiteId($rawPrice['website_id']);
        $price->setSku($sku);
        $price->setCustomerGroup(
            $rawPrice['all_groups'] == $this->allGroupsId
            ? $this->allGroupsValue
            : $this->customerGroupRepository->getById($rawPrice['customer_group_id'])->getCode()
        );
        $price->setQuantity($rawPrice['qty']);

        return $price;
    }

    /**
     * Build tier price skeleton that has DB consistent format.
     *
     * @param TierPriceInterface $price
     * @param int $id
     * @return array
     */
    public function createSkeleton(TierPriceInterface $price, $id)
    {
        return [
            $this->tierPricePersistence->getEntityLinkField() => $id,
            'all_groups' => $this->retrievePriceForAllGroupsValue($price),
            'customer_group_id' => $this->retrievePriceForAllGroupsValue($price) === $this->allGroupsId
                ? 0
                : $this->retrieveGroupValue(
                    $price->getCustomerGroup() === null ? '' : strtolower($price->getCustomerGroup())
                ),
            'qty' => $price->getQuantity(),
            'value' => $price->getPriceType() === TierPriceInterface::PRICE_TYPE_FIXED
                ? $price->getPrice()
                : 0.00,
            'percentage_value' => $price->getPriceType() === TierPriceInterface::PRICE_TYPE_DISCOUNT
                ? $price->getPrice()
                : null,
            'website_id' => $price->getWebsiteId()
        ];
    }

    /**
     * Retrieve price for all groups value.
     *
     * @param TierPriceInterface $price
     * @return int
     */
    private function retrievePriceForAllGroupsValue(TierPriceInterface $price)
    {
        if ($price->getCustomerGroup() === null) {
            return 0;
        }

        return strcasecmp($price->getCustomerGroup(), $this->allGroupsValue) === 0 ? $this->allGroupsId : 0;
    }

    /**
     * Retrieve customer group id by code.
     *
     * @param string $code
     * @return int
     * @throws NoSuchEntityException
     */
    private function retrieveGroupValue($code)
    {
        if (!isset($this->customerGroupsByCode[$code])) {
            $searchCriteria = $this->searchCriteriaBuilder->addFilters(
                [
                    $this->filterBuilder->setField('customer_group_code')->setValue($code)->create()
                ]
            );
            $items = $this->customerGroupRepository->getList($searchCriteria->create())->getItems();
            $item = array_shift($items);
            $this->customerGroupsByCode[strtolower($item->getCode())] = $item->getId();
        }

        return $this->customerGroupsByCode[$code];
    }
}
