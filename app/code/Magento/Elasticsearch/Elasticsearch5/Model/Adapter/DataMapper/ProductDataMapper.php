<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Elasticsearch5\Model\Adapter\DataMapper;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Elasticsearch\Model\Adapter\Container\Attribute as AttributeContainer;
use Magento\Elasticsearch\Model\Adapter\Document\Builder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Elasticsearch\Model\ResourceModel\Index;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\Model\Adapter\DataMapperInterface;
use Magento\Elasticsearch\Model\Adapter\FieldType\Date as DateFieldType;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeProvider;
use Magento\Framework\App\ObjectManager;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface;

/**
 * Don't use this product data mapper class.
 *
 * @deprecated 100.2.2
 * @see \Magento\Elasticsearch\Model\Adapter\BatchDataMapperInterface
 */
class ProductDataMapper implements DataMapperInterface
{
    /**
     * Attribute code for image
     */
    const MEDIA_ROLE_IMAGE = 'image';

    /**
     * Attribute code for small image
     */
    const MEDIA_ROLE_SMALL_IMAGE = 'small_image';

    /**
     * Attribute code for thumbnail
     */
    const MEDIA_ROLE_THUMBNAIL = 'thumbnail';

    /**
     * Attribute code for swatches
     */
    const MEDIA_ROLE_SWATCH_IMAGE = 'swatch_image';

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
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DateFieldType
     */
    private $dateFieldType;

    /**
     * Media gallery roles
     *
     * @var array
     */
    protected $mediaGalleryRoles;

    /**
     * @var AttributeProvider
     */
    private $attributeAdapterProvider;

    /**
     * @var ResolverInterface
     */
    private $fieldNameResolver;

    /**
     * Construction for DocumentDataMapper
     *
     * @param Builder $builder
     * @param AttributeContainer $attributeContainer
     * @param Index $resourceIndex
     * @param FieldMapperInterface $fieldMapper
     * @param StoreManagerInterface $storeManager
     * @param DateFieldType $dateFieldType
     * @param AttributeProvider|null $attributeAdapterProvider
     * @param ResolverInterface|null $fieldNameResolver
     */
    public function __construct(
        Builder $builder,
        AttributeContainer $attributeContainer,
        Index $resourceIndex,
        FieldMapperInterface $fieldMapper,
        StoreManagerInterface $storeManager,
        DateFieldType $dateFieldType,
        AttributeProvider $attributeAdapterProvider = null,
        ResolverInterface $fieldNameResolver = null
    ) {
        $this->builder = $builder;
        $this->attributeContainer = $attributeContainer;
        $this->resourceIndex = $resourceIndex;
        $this->fieldMapper = $fieldMapper;
        $this->storeManager = $storeManager;
        $this->dateFieldType = $dateFieldType;
        $this->attributeAdapterProvider = $attributeAdapterProvider ?: ObjectManager::getInstance()
            ->get(AttributeProvider::class);
        $this->fieldNameResolver = $fieldNameResolver ?: ObjectManager::getInstance()
            ->get(ResolverInterface::class);

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
     */
    public function map($productId, array $indexData, $storeId, $context = [])
    {
        $this->builder->addField('store_id', $storeId);
        if (count($indexData)) {
            $productIndexData = $this->resourceIndex->getFullProductIndexData($productId, $indexData);
        }

        foreach ($productIndexData as $attributeCode => $value) {
            // Prepare processing attribute info
            if (strpos($attributeCode, '_value') !== false) {
                $this->builder->addField($attributeCode, $value);
                continue;
            }
            $attribute = $this->attributeContainer->getAttribute($attributeCode);
            if (!$attribute ||
                in_array(
                    $attributeCode,
                    [
                        'price',
                        'media_gallery',
                        'tier_price',
                        'quantity_and_stock_status',
                        'media_gallery',
                        'giftcard_amounts'
                    ]
                )
            ) {
                continue;
            }
            $attribute->setStoreId($storeId);
            $value = $this->checkValue($value, $attribute, $storeId);
            $this->builder->addField(
                $this->fieldMapper->getFieldName(
                    $attributeCode,
                    $context
                ),
                $value
            );
        }
        $this->processAdvancedAttributes($productId, $productIndexData, $storeId);

        return $this->builder->build();
    }

    /**
     * Process advanced attribute values
     *
     * @param int $productId
     * @param array $productIndexData
     * @param int $storeId
     * @return void
     */
    protected function processAdvancedAttributes($productId, array $productIndexData, $storeId)
    {
        $mediaGalleryRoles = array_fill_keys($this->mediaGalleryRoles, '');
        $productPriceIndexData = $this->attributeContainer->getAttribute('price')
            ? $this->resourceIndex->getPriceIndexData([$productId], $storeId)
            : [];
        $productCategoryIndexData = $this->resourceIndex->getFullCategoryProductIndexData(
            $storeId,
            [$productId => $productId]
        );
        foreach ($productIndexData as $attributeCode => $value) {
            if (in_array($attributeCode, $this->mediaGalleryRoles)) {
                $mediaGalleryRoles[$attributeCode] = $value;
            } elseif ($attributeCode == 'tier_price') {
                $this->builder->addFields($this->getProductTierPriceData($value));
            } elseif ($attributeCode == 'quantity_and_stock_status') {
                $this->builder->addFields($this->getQtyAndStatus($value));
            } elseif ($attributeCode == 'media_gallery') {
                $this->builder->addFields(
                    $this->getProductMediaGalleryData(
                        $value,
                        $mediaGalleryRoles
                    )
                );
            }
        }
        $this->builder->addFields($this->getProductPriceData($productId, $storeId, $productPriceIndexData));
        $this->builder->addFields($this->getProductCategoryData($productId, $productCategoryIndexData));
    }

    /**
     * Check value.
     *
     * @param mixed $value
     * @param Attribute $attribute
     * @param string $storeId
     * @return array|mixed|null|string
     */
    protected function checkValue($value, $attribute, $storeId)
    {
        if (in_array($attribute->getBackendType(), ['datetime', 'timestamp'])
            || $attribute->getFrontendInput() === 'date') {
            return $this->dateFieldType->formatDate($storeId, $value);
        } elseif ($attribute->getFrontendInput() === 'multiselect') {
            return str_replace(',', ' ', $value);
        } else {
            return $value;
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
                $result['tier_price_id_' . $i] = $tierPrice['price_id'];
                $result['tier_website_id_' . $i] = $tierPrice['website_id'];
                $result['tier_all_groups_' . $i] = $tierPrice['all_groups'];
                $result['tier_cust_group_' . $i] = $tierPrice['cust_group'] == GroupInterface::CUST_GROUP_ALL
                    ? '' : $tierPrice['cust_group'];
                $result['tier_price_qty_' . $i] = $tierPrice['price_qty'];
                $result['tier_website_price_' . $i] = $tierPrice['website_price'];
                $result['tier_price_' . $i] = $tierPrice['price'];
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
     * Get media role image.
     *
     * @param string $file
     * @param array $roles
     * @return string
     */
    protected function getMediaRoleImage($file, $roles)
    {
        return $file == $roles[self::MEDIA_ROLE_IMAGE] ? '1' : '0';
    }

    /**
     * Get media role small image.
     *
     * @param string $file
     * @param array $roles
     * @return string
     */
    protected function getMediaRoleSmallImage($file, $roles)
    {
        return $file == $roles[self::MEDIA_ROLE_SMALL_IMAGE] ? '1' : '0';
    }

    /**
     * Get media role thumbnail.
     *
     * @param string $file
     * @param array $roles
     * @return string
     */
    protected function getMediaRoleThumbnail($file, $roles)
    {
        return $file == $roles[self::MEDIA_ROLE_THUMBNAIL] ? '1' : '0';
    }

    /**
     * Get media role swatch image.
     *
     * @param string $file
     * @param array $roles
     * @return string
     */
    protected function getMediaRoleSwatchImage($file, $roles)
    {
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
            foreach ($productPriceIndexData as $customerGroupId => $price) {
                $fieldName = $this->fieldMapper->getFieldName(
                    'price',
                    ['customerGroupId' => $customerGroupId, 'websiteId' => $storeId]
                );
                $result[$fieldName] = sprintf('%F', $price);
            }
        }
        return $result;
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
                $categoryIds[] = (int)$categoryData['id'];
            }
            if (count($categoryIds)) {
                $result = ['category_ids' => implode(' ', $categoryIds)];
                $positionAttribute = $this->attributeAdapterProvider->getByAttributeCode('position');
                $categoryNameAttribute = $this->attributeAdapterProvider->getByAttributeCode('category_name');
                foreach ($indexData as $data) {
                    $categoryPositionKey = $this->fieldNameResolver->getFieldName(
                        $positionAttribute,
                        ['categoryId' => $data['id']]
                    );
                    $categoryNameKey = $this->fieldNameResolver->getFieldName(
                        $categoryNameAttribute,
                        ['categoryId' => $data['id']]
                    );
                    $result[$categoryPositionKey] = $data['position'];
                    $result[$categoryNameKey] = $data['name'];
                }
            }
        }
        return $result;
    }
}
