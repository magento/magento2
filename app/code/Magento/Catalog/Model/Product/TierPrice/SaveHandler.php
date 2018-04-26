<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\TierPrice;

use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\EntityManager\TypeResolver;
use Magento\Catalog\Model\Attribute\ScopeOverriddenValue;
use Magento\Catalog\Model\Product\Attribute\Backend\Price;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice;

/**
 * Class SaveHandler
 */
class SaveHandler implements ExtensionInterface
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
     * @var \Magento\Customer\Api\GroupManagementInterface
     */
    private $groupManagement;

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
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice $tierPriceResource
    ) {
        $this->storeManager = $storeManager;
        $this->attributeRepository = $attributeRepository;
        $this->groupManagement = $groupManagement;
        $this->metadataPoll = $metadataPool;
        $this->tierPriceResource = $tierPriceResource;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface|object $entity
     * @param array $arguments
     * @return \Magento\Catalog\Api\Data\ProductInterface|object
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute($entity, $arguments = [])
    {
        $websiteId = $this->storeManager->getStore($entity->getStoreId())->getWebsiteId();
        $attribute = $this->attributeRepository->get('tier_price');
        $isGlobal = $attribute->isScopeGlobal() || $websiteId == 0;
        $priceRows = $entity->getData($attribute->getName());
        if (null === $priceRows) {
            return $entity;
        }

        $priceRows = array_filter((array)$priceRows);

        $identifierField = $this->metadataPoll->getMetadata(ProductInterface::class)->getLinkField();
        $productId = $entity->getData($identifierField);
        // prepare data for save
        foreach ($priceRows as $data) {
            $hasEmptyData = false;
            foreach ($this->getAdditionalUniqueFields($data) as $field) {
                if (empty($field)) {
                    $hasEmptyData = true;
                    break;
                }
            }

            if ($hasEmptyData || !isset($data['cust_group']) || !empty($data['delete'])) {
                continue;
            }
            if ($data['website_id'] > 0 && $attribute->isScopeGlobal()) {
                continue;
            }
            if (!$isGlobal && (int)$data['website_id'] === 0) {
                continue;
            }

            $useForAllGroups = $data['cust_group'] === $this->groupManagement->getAllCustomersGroup()->getId();
            $customerGroupId = !$useForAllGroups ? $data['cust_group'] : 0;
            $tierPrice = array_merge(
                $this->getAdditionalFields($data),
                [
                    'website_id' => $data['website_id'],
                    'all_groups' => $useForAllGroups ? 1 : 0,
                    'customer_group_id' => $customerGroupId,
                    'value' => $data['price'] ?? null
                ],
                $this->getAdditionalUniqueFields($data)
            );

            $price = new \Magento\Framework\DataObject($tierPrice);
            $price->setData(
                $this->metadataPoll->getMetadata(ProductInterface::class)->getLinkField(),
                $productId
            );
            $this->tierPriceResource->savePriceData($price);

            $valueChangedKey = $attribute->getName() . '_changed';
            $entity->setData($valueChangedKey, 1);
        }

        return $entity;
    }

    /**
     * Add price qty to unique fields
     *
     * @param array $objectArray
     * @return array
     */
    private function getAdditionalUniqueFields($objectArray)
    {
        $uniqueFields['qty'] = $objectArray['price_qty'] * 1;
        return $uniqueFields;
    }

    /**
     * Get additional tier price fields
     * @return array
     */
    private function getAdditionalFields($objectArray)
    {
        $percentageValue = $this->getPercentage($objectArray);
        return [
            'value' => $percentageValue ? null : $objectArray['price'],
            'percentage_value' => $percentageValue ?: null,
        ];
    }

    /**
     * Check whether price has percentage value.
     *
     * @param array $priceRow
     * @return integer|null
     */
    private function getPercentage($priceRow)
    {
        return isset($priceRow['percentage_value']) && is_numeric($priceRow['percentage_value'])
            ? $priceRow['percentage_value']
            : null;
    }
}
