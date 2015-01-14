<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product variations matrix block
 */
namespace Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Config;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Matrix extends \Magento\Backend\Block\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $_configurableType;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_applicationConfig;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_localeCurrency;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\VariationMatrix
     */
    protected $variationMatrix;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType
     * @param \Magento\Catalog\Model\Config $config
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\ConfigurableProduct\Model\Product\Type\VariationMatrix $variationMatrix
     * @param ProductRepositoryInterface $productRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType,
        \Magento\Catalog\Model\Config $config,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\ConfigurableProduct\Model\Product\Type\VariationMatrix $variationMatrix,
        ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_configurableType = $configurableType;
        $this->_config = $config;
        $this->_coreRegistry = $coreRegistry;
        $this->_localeCurrency = $localeCurrency;
        $this->stockRegistry = $stockRegistry;
        parent::__construct($context, $data);
        $this->variationMatrix = $variationMatrix;
        $this->productRepository = $productRepository;
    }

    /**
     * Retrieve price rendered according to current locale and currency settings
     *
     * @param int|float $price
     * @return string
     */
    public function renderPrice($price)
    {
        return $this->_localeCurrency->getCurrency(
            $this->_scopeConfig->getValue(\Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE, 'default')
        )->toCurrency(
            sprintf('%f', $price)
        );
    }

    /**
     * Retrieve currently edited product object
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    /**
     * Retrieve all possible attribute values combinations
     *
     * @return array
     */
    public function getVariations()
    {
        return $this->variationMatrix->getVariations($this->getAttributes());
    }

    /**
     * Get url for product edit
     *
     * @param string $id
     * @return string
     */
    public function getEditProductUrl($id)
    {
        return $this->getUrl('catalog/*/edit', ['id' => $id]);
    }

    /**
     * Retrieve attributes data
     *
     * @return array
     */
    public function getAttributes()
    {
        if (!$this->hasData('attributes')) {
            $attributes = (array)$this->_configurableType->getConfigurableAttributesAsArray($this->getProduct());
            $productData = (array)$this->getRequest()->getParam('product');
            if (isset($productData['configurable_attributes_data'])) {
                $configurableData = $productData['configurable_attributes_data'];
                foreach ($attributes as $key => $attribute) {
                    if (isset($configurableData[$key])) {
                        $attributes[$key] = array_replace_recursive($attribute, $configurableData[$key]);
                        $attributes[$key]['values'] = array_merge(
                            isset($attribute['values']) ? $attribute['values'] : [],
                            isset($configurableData[$key]['values'])
                            ? array_filter($configurableData[$key]['values'])
                            : []
                        );
                    }
                }
            }
            $this->setData('attributes', $attributes);
        }
        return $this->getData('attributes');
    }

    /**
     * Get used product attributes
     *
     * @return array
     */
    public function getUsedAttributes()
    {
        return $this->_configurableType->getUsedProductAttributes($this->getProduct());
    }

    /**
     * Retrieve actual list of associated products, array key is obtained from varying attributes values
     *
     * @return Product[]
     */
    public function getAssociatedProducts()
    {
        $productByUsedAttributes = [];
        foreach ($this->_getAssociatedProducts() as $product) {
            $keys = [];
            foreach ($this->getUsedAttributes() as $attribute) {
                /** @var $attribute \Magento\Catalog\Model\Resource\Eav\Attribute */
                $keys[] = $product->getData($attribute->getAttributeCode());
            }
            $productByUsedAttributes[implode('-', $keys)] = $product;
        }
        return $productByUsedAttributes;
    }

    /**
     * Retrieve actual list of associated products (i.e. if product contains variations matrix form data
     * - previously saved in database relations are not considered)
     *
     * @return Product[]
     */
    protected function _getAssociatedProducts()
    {
        $product = $this->getProduct();
        $ids = $this->getProduct()->getAssociatedProductIds();
        if ($ids === null) {
            // form data overrides any relations stored in database
            return $this->_configurableType->getUsedProducts($product);
        }
        $products = [];
        foreach ($ids as $productId) {
            try {
                $products[] = $this->productRepository->getById($productId);
            } catch (NoSuchEntityException $e) {
                continue;
            }
        }
        return $products;
    }

    /**
     * Get html class for attribute
     *
     * @param string $code
     * @return string
     */
    public function getAttributeFrontendClass($code)
    {
        /** @var $attribute \Magento\Catalog\Model\Resource\Eav\Attribute */
        $attribute = $this->_config->getAttribute(Product::ENTITY, $code);
        return $attribute instanceof
            \Magento\Eav\Model\Entity\Attribute\AbstractAttribute ? $attribute->getFrontend()->getClass() : '';
    }

    /**
     * Get url to upload files
     *
     * @return string
     */
    public function getImageUploadUrl()
    {
        return $this->getUrl('catalog/product_gallery/upload');
    }

    /**
     * @param Product $product
     * @return float
     */
    public function getProductStockQty(Product $product)
    {
        return $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId())->getQty();
    }
}
