<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Import\Product;

class SkuProcessor
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var array
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
     */
    protected $newSkus;

    /**
     * @var array
     */
    protected $productTypeModels;

    /**
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    public function __construct(\Magento\Catalog\Model\ProductFactory $productFactory)
    {
        $this->productFactory = $productFactory;
    }

    /**
     * @param array $typeModels
     * @return $this
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
     */
    public function addNewSku($sku, $data)
    {
        $this->newSkus[$sku] = $data;
        return $this;
    }

    /**
     * @param string $sku
     * @param string $key
     * @param mixed $data
     * @return $this
     */
    public function setNewSkuData($sku, $key, $data)
    {
        if ($this->newSkus[$sku]) {
            $this->newSkus[$sku][$key] = $data;
        }
        return $this;
    }

    /**
     * @param null|string $sku
     * @return array|null
     */
    public function getNewSku($sku = null)
    {
        if ($sku !== null) {
            return isset($this->newSkus[$sku]) ? $this->newSkus[$sku] : null;
        }
        return $this->newSkus;
    }

    /**
     * Get skus data.
     *
     * @return array
     */
    protected function _getSkus()
    {
        $oldSkus = [];
        $columns = ['entity_id', 'type_id', 'attribute_set_id', 'sku'];
        foreach ($this->productFactory->create()->getProductEntitiesInfo($columns) as $info) {
            $typeId = $info['type_id'];
            $sku = $info['sku'];
            $oldSkus[$sku] = [
                'type_id' => $typeId,
                'attr_set_id' => $info['attribute_set_id'],
                'entity_id' => $info['entity_id'],
                'supported_type' => isset($this->productTypeModels[$typeId]),
            ];
        }
        return $oldSkus;
    }
}
