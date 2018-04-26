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
 * Class UpdateHandler
 */
class UpdateHandler implements ExtensionInterface
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

        $old = [];
        $new = [];
        $identifierField = $this->metadataPoll->getMetadata(ProductInterface::class)->getLinkField();
        $productId = $entity->getData($identifierField);

        // prepare original data for compare
        $origPrices = $entity->getOrigData($attribute->getName());
        if (!is_array($origPrices)) {
            $origPrices = [];
        }
        foreach ($origPrices as $data) {
            if ($data['website_id'] > 0 || ($data['website_id'] === '0' && $isGlobal)) {
                $key = implode(
                    '-',
                    array_merge(
                        [$data['website_id'], $data['cust_group']],
                        $this->getAdditionalUniqueFields($data)
                    )
                );
                $old[$key] = $data;
            }
        }

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
            if ($attribute->isScopeGlobal() && $data['website_id'] > 0) {
                continue;
            }
            if (!$isGlobal && (int)$data['website_id'] == 0) {
                continue;
            }

            $key = implode(
                '-',
                array_merge([$data['website_id'], $data['cust_group']], $this->getAdditionalUniqueFields($data))
            );

            $useForAllGroups = $data['cust_group'] == $this->groupManagement->getAllCustomersGroup()->getId();
            $customerGroupId = !$useForAllGroups ? $data['cust_group'] : 0;
            $new[$key] = array_merge(
                $this->getAdditionalFields($data),
                [
                    'website_id' => $data['website_id'],
                    'all_groups' => $useForAllGroups ? 1 : 0,
                    'customer_group_id' => $customerGroupId,
                    'value' => isset($data['price']) ? $data['price'] : null,
                ],
                $this->getAdditionalUniqueFields($data)
            );
        }

        $delete = array_diff_key($old, $new);
        $insert = array_diff_key($new, $old);
        $update = array_intersect_key($new, $old);

        $isChanged = false;

        if (!empty($delete)) {
            foreach ($delete as $data) {
                $this->tierPriceResource->deletePriceData($productId, null, $data['price_id']);
                $isChanged = true;
            }
        }

        if (!empty($insert)) {
            foreach ($insert as $data) {
                $price = new \Magento\Framework\DataObject($data);
                $price->setData(
                    $this->metadataPoll->getMetadata(ProductInterface::class)->getLinkField(),
                    $productId
                );
                $this->tierPriceResource->savePriceData($price);

                $isChanged = true;
            }
        }

        if (!empty($update)) {
            $isChanged |= $this->updateValues($update, $old);
        }

        if ($isChanged) {
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

    /**
     * @param array $valuesToUpdate
     * @param array $oldValues
     * @return boolean
     */
    private function updateValues(array $valuesToUpdate, array $oldValues)
    {
        $isChanged = false;
        foreach ($valuesToUpdate as $key => $value) {
            if ($oldValues[$key]['price'] != $value['value']) {
                $price = new \Magento\Framework\DataObject(
                    [
                        'value_id' => $oldValues[$key]['price_id'],
                        'value' => $value['value']
                    ]
                );
                $this->tierPriceResource->savePriceData($price);
                $isChanged = true;
            }
        }
        return $isChanged;
    }
}
