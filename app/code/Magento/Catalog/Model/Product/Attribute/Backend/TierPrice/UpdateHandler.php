<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Attribute\Backend\TierPrice;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Locale\FormatInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice;

/**
 * Process tier price data for handled existing product.
 */
class UpdateHandler extends AbstractHandler
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
     * @var FormatInterface
     */
    private $localeFormat;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository
     * @param \Magento\Customer\Api\GroupManagementInterface $groupManagement
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice $tierPriceResource
     * @param FormatInterface|null $localeFormat
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ProductAttributeRepositoryInterface $attributeRepository,
        GroupManagementInterface $groupManagement,
        MetadataPool $metadataPool,
        Tierprice $tierPriceResource,
        FormatInterface $localeFormat = null
    ) {
        parent::__construct($groupManagement);

        $this->storeManager = $storeManager;
        $this->attributeRepository = $attributeRepository;
        $this->metadataPoll = $metadataPool;
        $this->tierPriceResource = $tierPriceResource;
        $this->localeFormat = $localeFormat ?: ObjectManager::getInstance()->get(FormatInterface::class);
    }

    /**
     * Perform action on relation/extension attribute.
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
            $websiteId = (int)$this->storeManager->getStore($entity->getStoreId())->getWebsiteId();
            $isGlobal = $attribute->isScopeGlobal() || $websiteId === 0;
            $identifierField = $this->metadataPoll->getMetadata(ProductInterface::class)->getLinkField();
            $productId = (int)$entity->getData($identifierField);

            // prepare original data to compare
            $origPrices = $entity->getOrigData($attribute->getName());
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
     * Update existing tier prices for processed product
     *
     * @param array $valuesToUpdate
     * @param array $oldValues
     * @return bool
     */
    private function updateValues(array $valuesToUpdate, array $oldValues): bool
    {
        $isChanged = false;
        foreach ($valuesToUpdate as $key => $value) {
            if ((!empty($value['value'])
                    && (float)$oldValues[$key]['price'] !== $this->localeFormat->getNumber($value['value'])
                ) || $this->getPercentage($oldValues[$key]) !== $this->getPercentage($value)
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
     * Insert new tier prices for processed product
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
     * Delete tier price values for processed product
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
     * Get generated price key based on price data
     *
     * @param array $priceData
     * @return string
     */
    private function getPriceKey(array $priceData): string
    {
        $qty = $this->parseQty($priceData['price_qty']);
        $key = implode(
            '-',
            array_merge([$priceData['website_id'], $priceData['cust_group']], [$qty])
        );

        return $key;
    }

    /**
     * Check by id is website global
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
    private function prepareOldTierPriceToCompare(?array $origPrices): array
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
    private function prepareNewDataForSave(array $priceRows, bool $isGlobal = true): array
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
