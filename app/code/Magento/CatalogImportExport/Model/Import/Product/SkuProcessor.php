<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Import\Product;

/**
 * Class SkuProcessor
 *
 * @api
 * @since 2.0.0
 */
class SkuProcessor
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     * @since 2.0.0
     */
    protected $productFactory;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $oldSkus;

    /**
     * Dry-runned products information from import file.
     *
     * [SKU] => array(
     *     'type_id'        => (string) product type
     *     'attr_set_id'    => (int) product attribute set ID
     *     'entity_id'      => (int) product ID (value for new products will be set after entity save)
     *     'attr_set_code'  => (string) attribute set code
     * )
     *
     * @var array
     * @since 2.0.0
     */
    protected $newSkus;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $productTypeModels;

    /**
     * Product metadata pool
     *
     * @var \Magento\Framework\EntityManager\MetadataPool
     * @since 2.1.0
     */
    private $metadataPool;

    /**
     * Product entity link field
     *
     * @var string
     * @since 2.1.0
     */
    private $productEntityLinkField;

    /**
     * Product entity identifier field
     *
     * @var string
     * @since 2.1.0
     */
    private $productEntityIdentifierField;

    /**
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        $this->productFactory = $productFactory;
    }

    /**
     * @param array $typeModels
     * @return $this
     * @since 2.0.0
     */
    public function setTypeModels($typeModels)
    {
        $this->productTypeModels = $typeModels;
        return $this;
    }

    /**
     * Get old skus array.
     *
     * @return array
     * @since 2.0.0
     */
    public function getOldSkus()
    {
        if (!$this->oldSkus) {
            $this->oldSkus = $this->_getSkus();
        }
        return $this->oldSkus;
    }

    /**
     * Reload old skus.
     *
     * @return $this
     * @since 2.0.0
     */
    public function reloadOldSkus()
    {
        $this->oldSkus = $this->_getSkus();

        return $this;
    }

    /**
     * @param string $sku
     * @param array $data
     * @return $this
     * @since 2.0.0
     */
    public function addNewSku($sku, $data)
    {
        $sku = strtolower($sku);
        $this->newSkus[$sku] = $data;
        return $this;
    }

    /**
     * @param string $sku
     * @param string $key
     * @param mixed $data
     * @return $this
     * @since 2.0.0
     */
    public function setNewSkuData($sku, $key, $data)
    {
        $sku = strtolower($sku);
        if (isset($this->newSkus[$sku])) {
            $this->newSkus[$sku][$key] = $data;
        }
        return $this;
    }

    /**
     * @param null|string $sku
     * @return array|null
     * @since 2.0.0
     */
    public function getNewSku($sku = null)
    {
        if ($sku !== null) {
            $sku = strtolower($sku);
            return isset($this->newSkus[$sku]) ? $this->newSkus[$sku] : null;
        }
        return $this->newSkus;
    }

    /**
     * Get skus data.
     *
     * @return array
     * @since 2.0.0
     */
    protected function _getSkus()
    {
        $oldSkus = [];
        $columns = ['entity_id', 'type_id', 'attribute_set_id', 'sku'];
        if ($this->getProductEntityLinkField() != $this->getProductIdentifierField()) {
            $columns[] = $this->getProductEntityLinkField();
        }
        foreach ($this->productFactory->create()->getProductEntitiesInfo($columns) as $info) {
            $typeId = $info['type_id'];
            $sku = strtolower($info['sku']);
            $oldSkus[$sku] = [
                'type_id' => $typeId,
                'attr_set_id' => $info['attribute_set_id'],
                'entity_id' => $info['entity_id'],
                'supported_type' => isset($this->productTypeModels[$typeId]),
                $this->getProductEntityLinkField() => $info[$this->getProductEntityLinkField()],
            ];
        }
        return $oldSkus;
    }

    /**
     * Get product metadata pool
     *
     * @return \Magento\Framework\EntityManager\MetadataPool
     * @since 2.1.0
     */
    private function getMetadataPool()
    {
        if (!$this->metadataPool) {
            $this->metadataPool = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\EntityManager\MetadataPool::class);
        }
        return $this->metadataPool;
    }

    /**
     * Get product entity link field
     *
     * @return string
     * @since 2.1.0
     */
    private function getProductEntityLinkField()
    {
        if (!$this->productEntityLinkField) {
            $this->productEntityLinkField = $this->getMetadataPool()
                ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
                ->getLinkField();
        }
        return $this->productEntityLinkField;
    }

    /**
     * Get product entity identifier field
     *
     * @return string
     * @since 2.1.0
     */
    private function getProductIdentifierField()
    {
        if (!$this->productEntityIdentifierField) {
            $this->productEntityIdentifierField = $this->getMetadataPool()
                ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
                ->getIdentifierField();
        }
        return $this->productEntityIdentifierField;
    }
}
