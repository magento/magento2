<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);

>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
namespace Magento\Catalog\Model\Product\Attribute\Backend\TierPrice;

use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice;

/**
<<<<<<< HEAD
 * Process tier price data for handled existing product.
 */
class UpdateHandler extends AbstractHandler
=======
 * Process tier price data for handled existing product
 */
class UpdateHandler implements ExtensionInterface
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
<<<<<<< HEAD
=======
     * @var \Magento\Customer\Api\GroupManagementInterface
     */
    private $groupManagement;

    /**
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
<<<<<<< HEAD
        parent::__construct($groupManagement);

        $this->storeManager = $storeManager;
        $this->attributeRepository = $attributeRepository;
=======
        $this->storeManager = $storeManager;
        $this->attributeRepository = $attributeRepository;
        $this->groupManagement = $groupManagement;
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
        $this->metadataPoll = $metadataPool;
        $this->tierPriceResource = $tierPriceResource;
    }

    /**
<<<<<<< HEAD
     * @param \Magento\Catalog\Api\Data\ProductInterface|object $entity
     * @param array $arguments
     * @return \Magento\Catalog\Api\Data\ProductInterface|object
     * @throws \Magento\Framework\Exception\InputException
=======
     * Perform action on relation/extension attribute.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface|object $entity
     * @param array $arguments
     * @return \Magento\Catalog\Api\Data\ProductInterface|object
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
            $websiteId = (int)$this->storeManager->getStore($entity->getStoreId())->getWebsiteId();
            $isGlobal = $attribute->isScopeGlobal() || $websiteId === 0;
            $identifierField = $this->metadataPoll->getMetadata(ProductInterface::class)->getLinkField();
            $productId = (int)$entity->getData($identifierField);

            // prepare original data to compare
<<<<<<< HEAD
            $origPrices = $entity->getOrigData($attribute->getName());
=======
            $origPrices = [];
            $originalId = $entity->getOrigData($identifierField);
            if (empty($originalId) || $entity->getData($identifierField) == $originalId) {
                $origPrices = $entity->getOrigData($attribute->getName());
            }

>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
            $old = $this->prepareOldTierPriceToCompare($origPrices);
            // prepare data for save
            $new = $this->prepareNewDataForSave($priceRows, $isGlobal);

            $delete = array_diff_key($old, $new);
            $insert = array_diff_key($new, $old);
            $update = array_intersect_key($new, $old);

            $isAttributeChanged = $this->deleteValues($productId, $delete);
            $isAttributeChanged |= $this->insertValues($productId, $insert);
            $isAttributeChanged |= $this->updateValues($update, $old);

            if ($isAttributeChanged) {
                $valueChangedKey = $attribute->getName() . '_changed';
                $entity->setData($valueChangedKey, 1);
            }
        }

        return $entity;
    }

    /**
<<<<<<< HEAD
=======
     * Get additional tier price fields
     *
     * @param array $objectArray
     * @return array
     */
    private function getAdditionalFields(array $objectArray): array
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
     * @return int|null
     */
    private function getPercentage(array $priceRow): ?int
    {
        return isset($priceRow['percentage_value']) && is_numeric($priceRow['percentage_value'])
            ? (int)$priceRow['percentage_value']
            : null;
    }

    /**
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     * Update existing tier prices for processed product
     *
     * @param array $valuesToUpdate
     * @param array $oldValues
<<<<<<< HEAD
     * @return boolean
=======
     * @return bool
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     */
    private function updateValues(array $valuesToUpdate, array $oldValues): bool
    {
        $isChanged = false;
        foreach ($valuesToUpdate as $key => $value) {
            if ((!empty($value['value']) && (float)$oldValues[$key]['price'] !== (float)$value['value'])
                || $this->getPercentage($oldValues[$key]) !== $this->getPercentage($value)
            ) {
                $price = new \Magento\Framework\DataObject(
                    [
                        'value_id' => $oldValues[$key]['price_id'],
                        'value' => $value['value'],
                        'percentage_value' => $this->getPercentage($value)
                    ]
                );
                $this->tierPriceResource->savePriceData($price);
                $isChanged = true;
            }
        }

        return $isChanged;
    }

    /**
<<<<<<< HEAD
     * Insert new tier prices for processed product.
=======
     * Insert new tier prices for processed product
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     *
     * @param int $productId
     * @param array $valuesToInsert
     * @return bool
     */
    private function insertValues(int $productId, array $valuesToInsert): bool
    {
        $isChanged = false;
        $identifierField = $this->metadataPoll->getMetadata(ProductInterface::class)->getLinkField();
        foreach ($valuesToInsert as $data) {
            $price = new \Magento\Framework\DataObject($data);
            $price->setData(
                $identifierField,
                $productId
            );
            $this->tierPriceResource->savePriceData($price);
            $isChanged = true;
        }

        return $isChanged;
    }

    /**
<<<<<<< HEAD
     * Delete tier price values for processed product.
=======
     * Delete tier price values for processed product
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     *
     * @param int $productId
     * @param array $valuesToDelete
     * @return bool
     */
    private function deleteValues(int $productId, array $valuesToDelete): bool
    {
        $isChanged = false;
        foreach ($valuesToDelete as $data) {
            $this->tierPriceResource->deletePriceData($productId, null, $data['price_id']);
            $isChanged = true;
        }

        return $isChanged;
    }

    /**
<<<<<<< HEAD
     * Get generated price key based on price data.
=======
     * Get generated price key based on price data
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     *
     * @param array $priceData
     * @return string
     */
    private function getPriceKey(array $priceData): string
    {
<<<<<<< HEAD
        $qty = $this->parseQty($priceData['price_qty']);
        $key = implode(
            '-',
            array_merge([$priceData['website_id'], $priceData['cust_group']], [$qty])
=======
        $key = implode(
            '-',
            array_merge([$priceData['website_id'], $priceData['cust_group']], [(int)$priceData['price_qty']])
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
        );

        return $key;
    }

    /**
<<<<<<< HEAD
     * Check by id is website global.
=======
     * Prepare tier price data by provided price row data
     *
     * @param array $data
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function prepareTierPrice(array $data): array
    {
        $useForAllGroups = (int)$data['cust_group'] === $this->groupManagement->getAllCustomersGroup()->getId();
        $customerGroupId = $useForAllGroups ? 0 : $data['cust_group'];
        $tierPrice = array_merge(
            $this->getAdditionalFields($data),
            [
                'website_id' => $data['website_id'],
                'all_groups' => (int)$useForAllGroups,
                'customer_group_id' => $customerGroupId,
                'value' => $data['price'] ?? null,
                'qty' => (int)$data['price_qty']
            ]
        );

        return $tierPrice;
    }

    /**
     * Check by id is website global
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     *
     * @param int $websiteId
     * @return bool
     */
    private function isWebsiteGlobal(int $websiteId): bool
    {
        return $websiteId === 0;
    }

    /**
     * Prepare old data to compare.
     *
     * @param array|null $origPrices
     * @return array
     */
<<<<<<< HEAD
    private function prepareOldTierPriceToCompare($origPrices): array
=======
    private function prepareOldTierPriceToCompare(?array $origPrices): array
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
    {
        $old = [];
        if (is_array($origPrices)) {
            foreach ($origPrices as $data) {
                $key = $this->getPriceKey($data);
                $old[$key] = $data;
            }
        }

        return $old;
    }

    /**
     * Prepare new data for save.
     *
     * @param array $priceRows
     * @param bool $isGlobal
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
<<<<<<< HEAD
    private function prepareNewDataForSave($priceRows, $isGlobal = true): array
=======
    private function prepareNewDataForSave(array $priceRows, bool $isGlobal = true): array
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
    {
        $new = [];
        $priceRows = array_filter($priceRows);
        foreach ($priceRows as $data) {
            if (empty($data['delete'])
                && (!empty($data['price_qty'])
                    || isset($data['cust_group'])
                    || $isGlobal === $this->isWebsiteGlobal((int)$data['website_id']))
            ) {
                $key = $this->getPriceKey($data);
                $new[$key] = $this->prepareTierPrice($data);
            }
        }

        return $new;
    }
}
