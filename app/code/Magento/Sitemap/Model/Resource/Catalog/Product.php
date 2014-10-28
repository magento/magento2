<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sitemap\Model\Resource\Catalog;

use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;

/**
 * Sitemap resource product collection model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Product extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    const NOT_SELECTED_IMAGE = 'no_selection';

    /**
     * Collection Zend Db select
     *
     * @var \Zend_Db_Select
     */
    protected $_select;

    /**
     * Attribute cache
     *
     * @var array
     */
    protected $_attributesCache = array();

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Backend\Media
     */
    protected $_mediaGalleryModel = null;

    /**
     * Init resource model (catalog/category)
     *
     */
    /**
     * Sitemap data
     *
     * @var \Magento\Sitemap\Helper\Data
     */
    protected $_sitemapData = null;

    /**
     * @var \Magento\Catalog\Model\Resource\Product
     */
    protected $_productResource;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_productVisibility;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $_productStatus;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\Backend\Media
     */
    protected $_mediaAttribute;

    /**
     * @var \Magento\Eav\Model\ConfigFactory
     */
    protected $_eavConfigFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $_mediaConfig;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Sitemap\Helper\Data $sitemapData
     * @param \Magento\Catalog\Model\Resource\Product $productResource
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\Backend\Media $mediaAttribute
     * @param \Magento\Eav\Model\ConfigFactory $eavConfigFactory
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Sitemap\Helper\Data $sitemapData,
        \Magento\Catalog\Model\Resource\Product $productResource,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\Resource\Product\Attribute\Backend\Media $mediaAttribute,
        \Magento\Eav\Model\ConfigFactory $eavConfigFactory,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig
    ) {
        $this->_productResource = $productResource;
        $this->_storeManager = $storeManager;
        $this->_productVisibility = $productVisibility;
        $this->_productStatus = $productStatus;
        $this->_mediaAttribute = $mediaAttribute;
        $this->_eavConfigFactory = $eavConfigFactory;
        $this->_mediaConfig = $mediaConfig;
        $this->_sitemapData = $sitemapData;
        parent::__construct($resource);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_entity', 'entity_id');
    }

    /**
     * Add attribute to filter
     *
     * @param int $storeId
     * @param string $attributeCode
     * @param mixed $value
     * @param string $type
     * @return \Zend_Db_Select|bool
     */
    protected function _addFilter($storeId, $attributeCode, $value, $type = '=')
    {
        if (!$this->_select instanceof \Zend_Db_Select) {
            return false;
        }

        switch ($type) {
            case '=':
                $conditionRule = '=?';
                break;
            case 'in':
                $conditionRule = ' IN(?)';
                break;
            default:
                return false;
                break;
        }

        $attribute = $this->_getAttribute($attributeCode);
        if ($attribute['backend_type'] == 'static') {
            $this->_select->where('e.' . $attributeCode . $conditionRule, $value);
        } else {
            $this->_joinAttribute($storeId, $attributeCode);
            if ($attribute['is_global']) {
                $this->_select->where('t1_' . $attributeCode . '.value' . $conditionRule, $value);
            } else {
                $ifCase = $this->_select->getAdapter()->getCheckSql(
                    't2_' . $attributeCode . '.value_id > 0',
                    't2_' . $attributeCode . '.value',
                    't1_' . $attributeCode . '.value'
                );
                $this->_select->where('(' . $ifCase . ')' . $conditionRule, $value);
            }
        }

        return $this->_select;
    }

    /**
     * Join attribute by code
     *
     * @param int $storeId
     * @param string $attributeCode
     * @return void
     */
    protected function _joinAttribute($storeId, $attributeCode)
    {
        $adapter = $this->getReadConnection();
        $attribute = $this->_getAttribute($attributeCode);
        $this->_select->joinLeft(
            array('t1_' . $attributeCode => $attribute['table']),
            'e.entity_id = t1_' . $attributeCode . '.entity_id AND ' . $adapter->quoteInto(
                ' t1_' . $attributeCode . '.store_id = ?',
                \Magento\Store\Model\Store::DEFAULT_STORE_ID
            ) . $adapter->quoteInto(
                ' AND t1_' . $attributeCode . '.attribute_id = ?',
                $attribute['attribute_id']
            ),
            array()
        );

        if (!$attribute['is_global']) {
            $this->_select->joinLeft(
                array('t2_' . $attributeCode => $attribute['table']),
                $this->_getWriteAdapter()->quoteInto(
                    't1_' .
                    $attributeCode .
                    '.entity_id = t2_' .
                    $attributeCode .
                    '.entity_id AND t1_' .
                    $attributeCode .
                    '.attribute_id = t2_' .
                    $attributeCode .
                    '.attribute_id AND t2_' .
                    $attributeCode .
                    '.store_id = ?',
                    $storeId
                ),
                array()
            );
        }
    }

    /**
     * Get attribute data by attribute code
     *
     * @param string $attributeCode
     * @return array
     */
    protected function _getAttribute($attributeCode)
    {
        if (!isset($this->_attributesCache[$attributeCode])) {
            $attribute = $this->_productResource->getAttribute($attributeCode);

            $this->_attributesCache[$attributeCode] = array(
                'entity_type_id' => $attribute->getEntityTypeId(),
                'attribute_id' => $attribute->getId(),
                'table' => $attribute->getBackend()->getTable(),
                'is_global' => $attribute->getIsGlobal() ==
                \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_GLOBAL,
                'backend_type' => $attribute->getBackendType()
            );
        }
        return $this->_attributesCache[$attributeCode];
    }

    /**
     * Get category collection array
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $storeId
     * @return array|bool
     */
    public function getCollection($storeId)
    {
        $products = array();

        /* @var $store \Magento\Store\Model\Store */
        $store = $this->_storeManager->getStore($storeId);
        if (!$store) {
            return false;
        }

        $adapter = $this->_getWriteAdapter();

        $this->_select = $adapter->select()->from(
            array('e' => $this->getMainTable()),
            array($this->getIdFieldName(), 'updated_at')
        )->joinInner(
            array('w' => $this->getTable('catalog_product_website')),
            'e.entity_id = w.product_id',
            array()
        )->joinLeft(
            ['url_rewrite' => $this->getTable('url_rewrite')],
            'e.entity_id = url_rewrite.entity_id AND url_rewrite.is_autogenerated = 1'
            . $adapter->quoteInto(' AND url_rewrite.store_id = ?', $store->getId())
            . $adapter->quoteInto(' AND url_rewrite.entity_type = ?', ProductUrlRewriteGenerator::ENTITY_TYPE),
            ['url' => 'request_path']
        )->where(
            'w.website_id = ?',
            $store->getWebsiteId()
        );

        $this->_addFilter($store->getId(), 'visibility', $this->_productVisibility->getVisibleInSiteIds(), 'in');
        $this->_addFilter($store->getId(), 'status', $this->_productStatus->getVisibleStatusIds(), 'in');

        // Join product images required attributes
        $imageIncludePolicy = $this->_sitemapData->getProductImageIncludePolicy($store->getId());
        if (\Magento\Sitemap\Model\Source\Product\Image\IncludeImage::INCLUDE_NONE != $imageIncludePolicy) {
            $this->_joinAttribute($store->getId(), 'name');
            $this->_select->columns(
                array('name' => $this->getReadConnection()->getIfNullSql('t2_name.value', 't1_name.value'))
            );

            if (\Magento\Sitemap\Model\Source\Product\Image\IncludeImage::INCLUDE_ALL == $imageIncludePolicy) {
                $this->_joinAttribute($store->getId(), 'thumbnail');
                $this->_select->columns(
                    array(
                        'thumbnail' => $this->getReadConnection()->getIfNullSql(
                            't2_thumbnail.value',
                            't1_thumbnail.value'
                        )
                    )
                );
            } elseif (\Magento\Sitemap\Model\Source\Product\Image\IncludeImage::INCLUDE_BASE == $imageIncludePolicy) {
                $this->_joinAttribute($store->getId(), 'image');
                $this->_select->columns(
                    array('image' => $this->getReadConnection()->getIfNullSql('t2_image.value', 't1_image.value'))
                );
            }
        }

        $query = $adapter->query($this->_select);
        while ($row = $query->fetch()) {
            $product = $this->_prepareProduct($row, $store->getId());
            $products[$product->getId()] = $product;
        }

        return $products;
    }

    /**
     * Prepare product
     *
     * @param array $productRow
     * @param int $storeId
     * @return \Magento\Framework\Object
     */
    protected function _prepareProduct(array $productRow, $storeId)
    {
        $product = new \Magento\Framework\Object();

        $product['id'] = $productRow[$this->getIdFieldName()];
        if (empty($productRow['url'])) {
            $productRow['url'] = 'catalog/product/view/id/' . $product->getId();
        }
        $product->addData($productRow);
        $this->_loadProductImages($product, $storeId);

        return $product;
    }

    /**
     * Load product images
     *
     * @param \Magento\Framework\Object $product
     * @param int $storeId
     * @return void
     */
    protected function _loadProductImages($product, $storeId)
    {
        /** @var $helper \Magento\Sitemap\Helper\Data */
        $helper = $this->_sitemapData;
        $imageIncludePolicy = $helper->getProductImageIncludePolicy($storeId);

        // Get product images
        $imagesCollection = array();
        if (\Magento\Sitemap\Model\Source\Product\Image\IncludeImage::INCLUDE_ALL == $imageIncludePolicy) {
            $imagesCollection = $this->_getAllProductImages($product, $storeId);
        } elseif (\Magento\Sitemap\Model\Source\Product\Image\IncludeImage::INCLUDE_BASE == $imageIncludePolicy &&
            $product->getImage() &&
            $product->getImage() != self::NOT_SELECTED_IMAGE
        ) {
            $imagesCollection = array(
                new \Magento\Framework\Object(
                    array('url' => $this->_getMediaConfig()->getBaseMediaUrlAddition() . $product->getImage())
                )
            );
        }

        if ($imagesCollection) {
            // Determine thumbnail path
            $thumbnail = $product->getThumbnail();
            if ($thumbnail && $product->getThumbnail() != self::NOT_SELECTED_IMAGE) {
                $thumbnail = $this->_getMediaConfig()->getBaseMediaUrlAddition() . $thumbnail;
            } else {
                $thumbnail = $imagesCollection[0]->getUrl();
            }

            $product->setImages(
                new \Magento\Framework\Object(
                    array('collection' => $imagesCollection, 'title' => $product->getName(), 'thumbnail' => $thumbnail)
                )
            );
        }
    }

    /**
     * Get all product images
     *
     * @param \Magento\Framework\Object $product
     * @param int $storeId
     * @return array
     */
    protected function _getAllProductImages($product, $storeId)
    {
        $product->setStoreId($storeId);
        $gallery = $this->_mediaAttribute->loadGallery($product, $this->_getMediaGalleryModel());

        $imagesCollection = array();
        if ($gallery) {
            $productMediaPath = $this->_getMediaConfig()->getBaseMediaUrlAddition();
            foreach ($gallery as $image) {
                $imagesCollection[] = new \Magento\Framework\Object(
                    array(
                        'url' => $productMediaPath . $image['file'],
                        'caption' => $image['label'] ? $image['label'] : $image['label_default']
                    )
                );
            }
        }

        return $imagesCollection;
    }

    /**
     * Get media gallery model
     *
     * @return \Magento\Catalog\Model\Product\Attribute\Backend\Media|null
     */
    protected function _getMediaGalleryModel()
    {
        if (is_null($this->_mediaGalleryModel)) {
            /** @var $eavConfig \Magento\Eav\Model\Config */
            $eavConfig = $this->_eavConfigFactory->create();
            /** @var $eavConfig \Magento\Eav\Model\Attribute */
            $mediaGallery = $eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'media_gallery');
            $this->_mediaGalleryModel = $mediaGallery->getBackend();
        }
        return $this->_mediaGalleryModel;
    }

    /**
     * Get media config
     *
     * @return \Magento\Catalog\Model\Product\Media\Config
     */
    protected function _getMediaConfig()
    {
        return $this->_mediaConfig;
    }
}
