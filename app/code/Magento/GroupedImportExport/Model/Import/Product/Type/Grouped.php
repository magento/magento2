<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedImportExport\Model\Import\Product\Type;

use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\CatalogImportExport\Model\Import\Product;
use Magento\CatalogImportExport\Model\Import\Product\SkuStorage;
use Magento\Framework\App\ObjectManager;
use Magento\ImportExport\Model\Import;

/**
 * Import entity of grouped product type
 */
class Grouped extends \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
{
    /**
     * Default delimiter for sku and qty.
     */
    public const SKU_QTY_DELIMITER = '=';

    /**
     * Column names that holds values with particular meaning.
     *
     * @var array
     */
    protected $_specialAttributes = ['_associated_sku', '_associated_default_qty', '_associated_position'];

    /**
     * @var Grouped\Links
     */
    protected $links;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var string[]
     */
    private $allowedProductTypes;

    /**
     * @var string
     */
    private $productEntityIdentifierField;

    /**
     * @var SkuStorage
     */
    private SkuStorage $skuStorage;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attrSetColFac
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $prodAttrColFac
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param array $params
     * @param Grouped\Links $links
     * @param ConfigInterface|null $config
     * @param SkuStorage|null $skuStorage
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attrSetColFac,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $prodAttrColFac,
        \Magento\Framework\App\ResourceConnection $resource,
        array $params,
        Grouped\Links $links,
        ConfigInterface $config = null,
        SkuStorage $skuStorage = null
    ) {
        $this->links = $links;
        $this->config = $config ?: ObjectManager::getInstance()->get(ConfigInterface::class);
        $this->allowedProductTypes = $this->config->getComposableTypes();
        parent::__construct($attrSetColFac, $prodAttrColFac, $resource, $params);
        $this->skuStorage = $skuStorage ?: ObjectManager::getInstance()
            ->get(SkuStorage::class);
    }

    /**
     * Save product type specific data.
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function saveData()
    {
        $newSku = $this->_entityModel->getNewSku();
        $attributes = $this->links->getAttributes();
        $productData = [];
        while ($bunch = $this->_entityModel->getNextBunch()) {
            $linksData = [
                'product_ids' => [],
                'attr_product_ids' => [],
                'position' => [],
                'qty' => [],
                'relation' => []
            ];
            foreach ($bunch as $rowNum => $rowData) {
                if ($this->_type != $rowData[Product::COL_TYPE]) {
                    continue;
                }
                $associatedSkusQty = isset($rowData['associated_skus']) ? $rowData['associated_skus'] : null;
                if (!$this->_entityModel->isRowAllowedToImport($rowData, $rowNum) || empty($associatedSkusQty)) {
                    continue;
                }
                $associatedSkusAndQtyPairs = explode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $associatedSkusQty);
                $position = 0;
                foreach ($associatedSkusAndQtyPairs as $associatedSkuAndQty) {
                    ++$position;
                    $associatedSkuAndQty = explode(self::SKU_QTY_DELIMITER, $associatedSkuAndQty);
                    $associatedSku = isset($associatedSkuAndQty[0]) ? strtolower(trim($associatedSkuAndQty[0])) : null;
                    if (isset($newSku[$associatedSku]) &&
                        in_array($newSku[$associatedSku]['type_id'], $this->allowedProductTypes)
                    ) {
                        $linkedProductId = $newSku[$associatedSku][$this->getProductEntityIdentifierField()];
                    } elseif ($associatedSku && $this->skuStorage->has($associatedSku) &&
                        in_array($this->skuStorage->get($associatedSku)['type_id'], $this->allowedProductTypes)
                    ) {
                        $oldProductData = $this->skuStorage->get($associatedSku);
                        $linkedProductId = $oldProductData[$this->getProductEntityIdentifierField()];
                    } else {
                        continue;
                    }
                    $scope = $this->_entityModel->getRowScope($rowData);
                    if (Product::SCOPE_DEFAULT == $scope) {
                        $productData = $newSku[strtolower($rowData[Product::COL_SKU])];
                    } else {
                        $colAttrSet = Product::COL_ATTR_SET;
                        $rowData[$colAttrSet] = $productData['attr_set_code'];
                        $rowData[Product::COL_TYPE] = $productData['type_id'];
                    }
                    $productId = $productData[$this->getProductEntityLinkField()];

                    $linksData['product_ids'][$productId] = true;
                    $linksData['relation'][] = ['parent_id' => $productId, 'child_id' => $linkedProductId];
                    $qty = empty($associatedSkuAndQty[1]) ? 0 : trim($associatedSkuAndQty[1]);
                    $linksData['attr_product_ids'][$productId] = true;
                    $linksData['position']["{$productId} {$linkedProductId}"] = [
                        'product_link_attribute_id' => $attributes['position']['id'],
                        'value' => $position
                    ];
                    if ($qty) {
                        $linksData['attr_product_ids'][$productId] = true;
                        $linksData['qty']["{$productId} {$linkedProductId}"] = [
                            'product_link_attribute_id' => $attributes['qty']['id'],
                            'value' => $qty
                        ];
                    }
                }
            }
            $this->links->saveLinksData($linksData, $this->_entityModel);
        }
        return $this;
    }

    /**
     * Get product entity identifier field
     *
     * @return string
     */
    private function getProductEntityIdentifierField()
    {
        if (!$this->productEntityIdentifierField) {
            $this->productEntityIdentifierField = $this->getMetadataPool()
                ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
                ->getIdentifierField();
        }
        return $this->productEntityIdentifierField;
    }
}
