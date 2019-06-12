<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
namespace Magento\Catalog\Model\Product\Attribute\Backend\TierPrice;

use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
=======
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Attribute\Backend\TierPrice;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Locale\FormatInterface;
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice;

/**
<<<<<<< HEAD
 * Process tier price data for handled existing product
 */
class UpdateHandler implements ExtensionInterface
=======
 * Process tier price data for handled existing product.
 */
class UpdateHandler extends AbstractHandler
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
     * @var \Magento\Customer\Api\GroupManagementInterface
     */
    private $groupManagement;

    /**
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPoll;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice
     */
    private $tierPriceResource;

    /**
<<<<<<< HEAD
=======
     * @var FormatInterface
     */
    private $localeFormat;

    /**
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository
     * @param \Magento\Customer\Api\GroupManagementInterface $groupManagement
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice $tierPriceResource
<<<<<<< HEAD
=======
     * @param FormatInterface|null $localeFormat
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ProductAttributeRepositoryInterface $attributeRepository,
        GroupManagementInterface $groupManagement,
        MetadataPool $metadataPool,
<<<<<<< HEAD
        Tierprice $tierPriceResource
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
=======
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
     * @return integer|null
     */
    private function getPercentage(array $priceRow)
    {
        return isset($priceRow['percentage_value']) && is_numeric($priceRow['percentage_value'])
            ? (int)$priceRow['percentage_value']
            : null;
    }

    /**
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * Update existing tier prices for processed product
     *
     * @param array $valuesToUpdate
     * @param array $oldValues
<<<<<<< HEAD
     * @return boolean
=======
     * @return bool
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private function updateValues(array $valuesToUpdate, array $oldValues): bool
    {
        $isChanged = false;
        foreach ($valuesToUpdate as $key => $value) {
<<<<<<< HEAD
            if ((!empty($value['value']) && (float)$oldValues[$key]['price'] !== (float)$value['value'])
                || $this->getPercentage($oldValues[$key]) !== $this->getPercentage($value)
=======
            if ((!empty($value['value'])
                    && (float)$oldValues[$key]['price'] !== $this->localeFormat->getNumber($value['value'])
                ) || $this->getPercentage($oldValues[$key]) !== $this->getPercentage($value)
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
        $key = implode(
            '-',
            array_merge([$priceData['website_id'], $priceData['cust_group']], [(int)$priceData['price_qty']])
=======
        $qty = $this->parseQty($priceData['price_qty']);
        $key = implode(
            '-',
            array_merge([$priceData['website_id'], $priceData['cust_group']], [$qty])
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        );

        return $key;
    }

    /**
<<<<<<< HEAD
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
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
    private function prepareOldTierPriceToCompare($origPrices): array
=======
    private function prepareOldTierPriceToCompare(?array $origPrices): array
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
=======
     * Prepare new data for save.
     *
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @param array $priceRows
     * @param bool $isGlobal
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
<<<<<<< HEAD
    private function prepareNewDataForSave($priceRows, $isGlobal = true): array
=======
    private function prepareNewDataForSave(array $priceRows, bool $isGlobal = true): array
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
