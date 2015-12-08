<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Elasticsearch\Model\Adapter\Container\Attribute as AttributeContainer;
use Magento\Elasticsearch\Model\Adapter\Document\Builder;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\Data\GroupInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class DocumentDataMapper
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
     * @var \Magento\Elasticsearch\Model\ResourceModel\Index
     */
    private $resourceIndex;

    /**
     * @var FieldMapper
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
     * @param \Magento\Elasticsearch\Model\ResourceModel\Index $resourceIndex
     * @param FieldMapper $fieldMapper
     * @param DateTime $dateTime
     * @param TimezoneInterface $localeDate
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Builder $builder,
        AttributeContainer $attributeContainer,
        \Magento\Elasticsearch\Model\ResourceModel\Index $resourceIndex,
        FieldMapper $fieldMapper,
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
     * @param array $productIndexData
     * @param int $storeId
     * @return array|false
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function map($productId, array $productIndexData, $storeId)
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
        if (count($productIndexData[$productId])) {
            $productIndexData = $this->resourceIndex->getFullProductIndexData([$productId]);
        }

        foreach ($productIndexData[$productId] as $attributeCode => $value) {
            // Prepare processing attribute info
            if (strpos($attributeCode, '_value') !== false) {
                $this->builder->addField($attributeCode, $value);
                continue;
            }
            /* @var Attribute|null $attribute */
            $attribute = $this->attributeContainer->getAttribute($attributeCode);
            if (!$attribute || $attributeCode === 'price') {
                continue;
            }

            $attribute->setStoreId($storeId);
            if (in_array($attributeCode, $this->mediaGalleryRoles)) {
                $mediaGalleryRoles[$attributeCode] = $value;
            }
            if ($attributeCode === 'media_gallery') {
                $this->builder->addField(
                    'media_gallery',
                    $this->getProductMediaGalleryData($value, $mediaGalleryRoles)
                );
                continue;
            }
            if ($attributeCode === 'quantity_and_stock_status') {
                if (!is_array($value)) {
                    $value = [
                        'is_in_stock' => $value ? 1 : 0,
                        'qty' => $value
                    ];
                }
                $this->builder->addField($this->fieldMapper->getFieldName($attributeCode), $value);
                continue;
            }
            if ($attributeCode === 'tier_price') {
                $this->builder->addField('tier_price', $this->getProductTierPriceData($value));
                continue;
            }
            if (is_array($value)) {
                $value = array_shift($value);
            }

            if ($attribute->getBackendType() === 'datetime' || $attribute->getBackendType() === 'timestamp'
                || $attribute->getFrontendInput() === 'date') {
                $value = $this->formatDate($storeId, $value);
            }

            $this->builder->addField($this->fieldMapper->getFieldName($attributeCode), $value);

            unset($attribute);
        }

        $this->builder->addField('price', $this->getProductPriceData($productId, $storeId, $productPriceIndexData));
        $this->builder->addField('category', $this->getProductCategoryData($productId, $productCategoryIndexData));

        return $this->builder->build();
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
            foreach ($data as $tierPrice) {
                $result[] = [
                    'price_id' => $tierPrice['price_id'],
                    'website_id' => $tierPrice['website_id'],
                    'all_groups' => $tierPrice['all_groups'],
                    'cust_group' => $tierPrice['cust_group'] == GroupInterface::CUST_GROUP_ALL
                        ? '' : $tierPrice['cust_group'],
                    'price_qty' => $tierPrice['price_qty'],
                    'website_price' => $tierPrice['website_price'],
                    'price' => $tierPrice['price'],
                ];
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
                $result[$i] = [
                    'file' => $data['file'],
                    'media_type' => $data['media_type'],
                    'position' => $data['position'],
                    'disabled' => $data['disabled'],
                    'label' => $data['label'],
                    'title' => $data['label'],
                    'base_image' => $data['file'] == $roles[self::MEDIA_ROLE_IMAGE] ? '1' : '0',
                    'small_image' => $data['file'] == $roles[self::MEDIA_ROLE_SMALL_IMAGE] ? '1' : '0',
                    'thumbnail' => $data['file'] == $roles[self::MEDIA_ROLE_THUMBNAIL] ? '1' : '0',
                    'swatch_image' => $data['file'] == $roles[self::MEDIA_ROLE_SWATCH_IMAGE] ? '1' : '0'
                ];
                if ($data['media_type'] !== 'image') {
                    $video = [
                        'video_title' => $data['video_title'],
                        'video_url' => $data['video_url'],
                        'video_description' => $data['video_description'],
                        'video_metadata' => $data['video_metadata'],
                        'video_provider' => $data['video_provider']
                    ];
                    $result[$i] = array_merge($result[$i], $video);
                }
                $i++;
            }
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
                $result[] = [
                    'price' => $price,
                    'customer_group_id' => $customerGroupId,
                    'website_id' => $websiteId
                ];
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

        if (array_key_exists($productId, $categoryIndexData)) {
            $indexData = $categoryIndexData[$productId];
            $result = $indexData;
        }

        return $result;
    }
}
