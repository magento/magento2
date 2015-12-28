<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\DataMapper;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Elasticsearch\Model\Adapter\Container\Attribute as AttributeContainer;
use Magento\Elasticsearch\Model\Adapter\Document\Builder;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Elasticsearch\Model\ResourceModel\Index;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\Model\Adapter\DataMapperInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class ProductDataMapper implements DataMapperInterface
{
    /**
     * Attribute code for image.
     */
    const MEDIA_ROLE_IMAGE = 'image';

    /**
     * Attribute code for small image.
     */
    const MEDIA_ROLE_SMALL_IMAGE = 'small_image';

    /**
     * Attribute code for thumbnail.
     */
    const MEDIA_ROLE_THUMBNAIL = 'thumbnail';

    /**
     * Attribute code for swatches.
     */
    const MEDIA_ROLE_SWATCH_IMAGE = 'swatch_image';

    /**
     * Entyity type for product.
     */
    const PRODUCT_ENTITY_TYPE = 'product';

    /**
     * Array of \DateTime objects per store
     *
     * @var \DateTime[]
     */
    protected $dateFormats = [];

    /**
     * @var Builder
     */
    private $builder;

    /**
     * @var AttributeContainer
     */
    private $attributeContainer;

    /**
     * @var Index
     */
    private $resourceIndex;

    /**
     * @var FieldMapperInterface
     */
    private $fieldMapper;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Media gallery roles
     *
     * @var array
     */
    protected $mediaGalleryRoles;

    /**
     * Construction for DocumentDataMapper
     *
     * @param Builder $builder
     * @param AttributeContainer $attributeContainer
     * @param Index $resourceIndex
     * @param FieldMapperInterface $fieldMapper
     * @param DateTime $dateTime
     * @param TimezoneInterface $localeDate
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Builder $builder,
        AttributeContainer $attributeContainer,
        Index $resourceIndex,
        FieldMapperInterface $fieldMapper,
        DateTime $dateTime,
        TimezoneInterface $localeDate,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->builder = $builder;
        $this->attributeContainer = $attributeContainer;
        $this->resourceIndex = $resourceIndex;
        $this->fieldMapper = $fieldMapper;
        $this->dateTime = $dateTime;
        $this->localeDate = $localeDate;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;

        $this->mediaGalleryRoles = [
            self::MEDIA_ROLE_IMAGE,
            self::MEDIA_ROLE_SMALL_IMAGE,
            self::MEDIA_ROLE_THUMBNAIL,
            self::MEDIA_ROLE_SWATCH_IMAGE
        ];
    }

    /**
     * Prepare index data for using in search engine metadata.
     *
     * @param int $productId
     * @param array $indexData
     * @param int $storeId
     * @param array $context
     * @return array|false
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function map($productId, array $indexData, $storeId, $context = [])
    {
        $this->builder->addField('store_id', $storeId);
        $mediaGalleryRoles = array_fill_keys($this->mediaGalleryRoles, '');

        $productPriceIndexData = $this->attributeContainer->getAttribute('price')
            ? $this->resourceIndex->getPriceIndexData([$productId], $storeId)
            : [];
        $productCategoryIndexData = $this->resourceIndex->getFullCategoryProductIndexData(
            $storeId,
            [$productId => $productId]
        );
        if (count($indexData)) {
            $productIndexData = $this->resourceIndex->getFullProductIndexData($productId, $indexData);
        }

        foreach ($productIndexData as $attributeCode => $value) {
            // Prepare processing attribute info
            if (strpos($attributeCode, '_value') !== false) {
                $this->builder->addField($attributeCode, $value);
                continue;
            }
            /* @var Attribute|null $attribute */
            $attribute = $this->attributeContainer->getAttribute($attributeCode);
            if (!$attribute
                || in_array($attributeCode, ['price', 'media_gallery'], true)
                ) {
                continue;
            }

            if ($attributeCode === 'tier_price') {
                $this->builder->addFields($this->getProductTierPriceData($value));
                continue;
            }

            if ($attributeCode === 'quantity_and_stock_status') {
                $this->builder->addFields($this->getQtyAndStatus($value));
                continue;
            }

            if ($attributeCode === 'media_gallery') {
                $this->builder->addFields($this->getProductMediaGalleryData(
                    $value, $mediaGalleryRoles)
                );
                continue;
            }

            $attribute->setStoreId($storeId);
            $mediaGalleryRoles[$attributeCode] = $this->getMediaGalleryRole($attributeCode, $value);
            $value = $this->checkValue($value, $attribute, $storeId);

            $this->builder->addField($this->fieldMapper->getFieldName(
                $attributeCode, ['entityType' => self::PRODUCT_ENTITY_TYPE]
            ), $value);

            unset($attribute);
        }

        $this->builder->addFields($this->getProductPriceData($productId, $storeId, $productPriceIndexData));
        $this->builder->addFields($this->getProductCategoryData($productId, $productCategoryIndexData));

        return $this->builder->build();
    }

    /**
     * @param mixed $value
     * @param Attribute $attribute
     * @param string $storeId
     * @return array|mixed|null|string
     */
    protected function checkValue($value, $attribute, $storeId) {
        if (is_array($value)) {
            return array_shift($value);
        } elseif ($attribute->getBackendType() === 'datetime' || $attribute->getBackendType() === 'timestamp'
            || $attribute->getFrontendInput() === 'date') {
            return $this->formatDate($storeId, $value);
        } else {
            return $value;
        }
    }

    /**
     * @param string $attributeCode
     * @param string $value
     * @return mixed
     */
    protected function getMediaGalleryRole($attributeCode, $value) {
        if (in_array($attributeCode, $this->mediaGalleryRoles)) {
            return  $value;
        }
    }

    /**
     * Prepare tier price data for product
     *
     * @param array $data
     * @return array
     */
    protected function getProductTierPriceData($data)
    {
        $result = [];
        if (!empty($data)) {
            $i = 0;
            foreach ($data as $tierPrice) {
                $result['tier_price_id_'.$i] = $tierPrice['price_id'];
                $result['tier_website_id_'.$i] = $tierPrice['website_id'];
                $result['tier_all_groups_'.$i] = $tierPrice['all_groups'];
                $result['tier_cust_group_'.$i] = $tierPrice['cust_group'] == GroupInterface::CUST_GROUP_ALL
                    ? '' : $tierPrice['cust_group'];
                $result['tier_price_qty_'.$i] = $tierPrice['price_qty'];
                $result['tier_website_price_'.$i] = $tierPrice['website_price'];
                $result['tier_price_'.$i] = $tierPrice['price'];
                $i++;
            }
        }

        return $result;
    }

    /**
     * Prepare media gallery data for product
     *
     * @param array $media
     * @param array $roles
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function getProductMediaGalleryData($media, $roles)
    {
        $result = [];

        if (!empty($media['images'])) {
            $i = 0;
            foreach ($media['images'] as $data) {
                if ($data['media_type'] === 'image') {
                    $result['image_file_' . $i] = $data['file'];
                    $result['image_position_' . $i] = $data['position'];
                    $result['image_disabled_' . $i] = $data['disabled'];
                    $result['image_label_' . $i] = $data['label'];
                    $result['image_title_' . $i] = $data['label'];
                    $result['image_base_image_' . $i] = $this->getMediaRoleImage($data['file'], $roles);
                    $result['image_small_image_' . $i] = $this->getMediaRoleSmallImage($data['file'], $roles);
                    $result['image_thumbnail_' . $i] = $this->getMediaRoleThumbnail($data['file'], $roles);
                    $result['image_swatch_image_' . $i] = $this->getMediaRoleSwatchImage($data['file'], $roles);
                } else {
                    $result['video_file_' . $i] = $data['file'];
                    $result['video_position_' . $i] = $data['position'];
                    $result['video_disabled_' . $i] = $data['disabled'];
                    $result['video_label_' . $i] = $data['label'];
                    $result['video_title_' . $i] = $data['video_title'];
                    $result['video_base_image_' . $i] = $this->getMediaRoleImage($data['file'], $roles);
                    $result['video_small_image_' . $i] = $this->getMediaRoleSmallImage($data['file'], $roles);
                    $result['video_thumbnail_' . $i] = $this->getMediaRoleThumbnail($data['file'], $roles);
                    $result['video_swatch_image_' . $i] = $this->getMediaRoleSwatchImage($data['file'], $roles);
                    $result['video_url_' . $i] = $data['video_url'];
                    $result['video_description_' . $i] = $data['video_description'];
                    $result['video_metadata_' . $i] = $data['video_metadata'];
                    $result['video_provider_' . $i] = $data['video_provider'];
                }
                $i++;
            }
        }
        return $result;
    }

    /**
     * @param string $file
     * @param array $roles
     * @return string
     */
    protected function getMediaRoleImage($file, $roles) {
        return $file == $roles[self::MEDIA_ROLE_IMAGE] ? '1' : '0';
    }

    /**
     * @param string $file
     * @param array $roles
     * @return string
     */
    protected function getMediaRoleSmallImage($file, $roles) {
        return $file == $roles[self::MEDIA_ROLE_SMALL_IMAGE] ? '1' : '0';
    }

    /**
     * @param string $file
     * @param array $roles
     * @return string
     */
    protected function getMediaRoleThumbnail($file, $roles) {
        return $file == $roles[self::MEDIA_ROLE_THUMBNAIL] ? '1' : '0';
    }

    /**
     * @param string $file
     * @param array $roles
     * @return string
     */
    protected function getMediaRoleSwatchImage($file, $roles) {
        return $file == $roles[self::MEDIA_ROLE_SWATCH_IMAGE] ? '1' : '0';
    }

    /**
     * Prepare quantity and stock status for product
     *
     * @param array $data
     * @return array
     */
    protected function getQtyAndStatus($data)
    {
        $result = [];

        if (!is_array($data)) {
            $result['is_in_stock'] = $data ? 1 : 0;
            $result['qty'] = $data;
        } else {
            $result['is_in_stock'] = $data['is_in_stock'] ? 1 : 0;
            $result['qty'] = $data['qty'];
        }
        return $result;
    }

    /**
     * Prepare price index for product
     *
     * @param int $productId
     * @param int $storeId
     * @param array $priceIndexData
     * @return array
     */
    protected function getProductPriceData($productId, $storeId, array $priceIndexData)
    {
        $result = [];

        if (array_key_exists($productId, $priceIndexData)) {
            $productPriceIndexData = $priceIndexData[$productId];

            $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
            foreach ($productPriceIndexData as $customerGroupId => $price) {
                $fieldName = 'price_' . $customerGroupId . '_' . $websiteId;
                $result[$fieldName] = sprintf('%F', $price);
            }
        }
        return $result;
    }

    /**
     * Retrieve date value in elasticseacrh format (ISO 8601) with Z
     * Example: 1995-12-31T23:59:59Z
     *
     * @param int $storeId
     * @param string|null $date
     * @return string|null
     */
    protected function formatDate($storeId, $date = null)
    {
        if ($this->dateTime->isEmptyDate($date)) {
            return null;
        }

        if (!array_key_exists($storeId, $this->dateFormats)) {
            $timezone = $this->scopeConfig->getValue(
                $this->localeDate->getDefaultTimezonePath(),
                ScopeInterface::SCOPE_STORE,
                $storeId
            );

            $dateObj = new \DateTime();
            $dateObj->setTimezone(new \DateTimeZone($timezone));
            $this->dateFormats[$storeId] = $dateObj;
        }

        $dateObj = $this->dateFormats[$storeId];
        return $dateObj->format('c');
    }

    /**
     * Prepare category index data for product
     *
     * @param int $productId
     * @param array $categoryIndexData
     * @return array
     */
    protected function getProductCategoryData($productId, array $categoryIndexData)
    {
        $result = [];
        $categoryIds = [];

        if (array_key_exists($productId, $categoryIndexData)) {
            $indexData = $categoryIndexData[$productId];
            $result = $indexData;
        }

        if (array_key_exists($productId, $categoryIndexData)) {
            $indexData = $categoryIndexData[$productId];

            foreach ($indexData as $categoryData) {
                $categoryIds[] = $categoryData['id'];
            }
            if (count($categoryIds)) {
                $result = ['category_ids' => implode(' ', $categoryIds)];

                foreach ($indexData as $data) {
                    $result['position_category_' . $data['id']] = $data['position'];
                    $result['name_category_' . $data['id']] = $data['name'];
                }
            }
        }
        return $result;
    }
}
