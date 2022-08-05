<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Attribute\Backend\TierPrice;

use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice;

/**
 * Process tier price data for handled new product
 */
class SaveHandler extends AbstractHandler
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPoll;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice
     */
    private $tierPriceResource;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository
     * @param \Magento\Customer\Api\GroupManagementInterface $groupManagement
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice $tierPriceResource
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ProductAttributeRepositoryInterface $attributeRepository,
        GroupManagementInterface $groupManagement,
        MetadataPool $metadataPool,
        Tierprice $tierPriceResource
    ) {
        parent::__construct($groupManagement);

        $this->storeManager = $storeManager;
        $this->attributeRepository = $attributeRepository;
        $this->metadataPoll = $metadataPool;
        $this->tierPriceResource = $tierPriceResource;
    }

    /**
     * Set tier price data for product entity
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface|object $entity
     * @param array $arguments
     * @return \Magento\Catalog\Api\Data\ProductInterface|object
     * @throws \Magento\Framework\Exception\InputException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $attribute = $this->attributeRepository->get('tier_price');
        $priceRows = $entity->getData($attribute->getName());
        if (null !== $priceRows) {
            if (!is_array($priceRows)) {
                throw new \Magento\Framework\Exception\InputException(
                    __('Tier prices data should be array, but actually other type is received')
                );
            }
            $websiteId = $this->storeManager->getStore($entity->getStoreId())->getWebsiteId();
            $isGlobal = $attribute->isScopeGlobal() || $websiteId === 0;
            $identifierField = $this->metadataPoll->getMetadata(ProductInterface::class)->getLinkField();
            $priceRows = array_filter($priceRows);
            $productId = (int) $entity->getData($identifierField);
            $pricesStored = $this->getPricesStored($priceRows);
            $pricesMerged = $this->mergePrices($priceRows, $pricesStored);

            // prepare and save data
            foreach ($pricesMerged as $data) {
                $isPriceWebsiteGlobal = (int)$data['website_id'] === 0;
                if ($isGlobal === $isPriceWebsiteGlobal
                    || !empty($data['price_qty'])
                    || isset($data['cust_group'])
                ) {
                    $tierPrice = $this->prepareTierPrice($data);
                    $price = new \Magento\Framework\DataObject($tierPrice);
                    $price->setData(
                        $identifierField,
                        $productId
                    );
                    $this->tierPriceResource->savePriceData($price);
                    $valueChangedKey = $attribute->getName() . '_changed';
                    $entity->setData($valueChangedKey, 1);
                }
            }
        }

        return $entity;
    }

    /**
     * Merge prices
     *
     * @param array $prices
     * @param array $pricesStored
     * @return array
     */
    private function mergePrices(array $prices, array $pricesStored): array
    {
        if (!$pricesStored) {
            return $prices;
        }
        $pricesId = [];
        $pricesStoredId = [];
        foreach ($prices as $price) {
            if (isset($price['price_id'])) {
                $pricesId[$price['price_id']] = $price;
            }
        }
        foreach ($pricesStored as $price) {
            if (isset($price['price_id'])) {
                $pricesStoredId[$price['price_id']] = $price;
            }
        }
        $pricesAdd = array_diff_key($pricesStoredId, $pricesId);
        foreach ($pricesAdd as $price) {
            $prices[] = $price;
        }
        return $prices;
    }

    /**
     * Get stored prices
     *
     * @param array $prices
     * @return array
     */
    private function getPricesStored(array $prices): array
    {
        $pricesStored = [];
        $price = reset($prices);
        if (isset($price['product_id']) && $price['product_id']) {
            $pricesStored = $this->tierPriceResource->loadPriceData($price['product_id']);
        }
        return $pricesStored;
    }
}
