<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

/**
 * Product description block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Product\View;

use Magento\Catalog\Model\Product;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Phrase;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Attributes attributes block
 *
 * @api
 * @since 100.0.2
 */
class Attributes extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Product
     */
    protected $_product = null;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var DirectoryHelper
     */
    private DirectoryHelper $directoryHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     * @param DirectoryHelper|null $directoryHelper
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        PriceCurrencyInterface $priceCurrency,
        array $data = [],
        ?DirectoryHelper $directoryHelper = null
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->_coreRegistry = $registry;
        $this->directoryHelper = $directoryHelper
            ?? \Magento\Framework\App\ObjectManager::getInstance()->get(DirectoryHelper::class);
        parent::__construct($context, $data);
    }

    /**
     * Returns a Product
     *
     * @return Product
     */
    public function getProduct()
    {
        if (!$this->_product) {
            $this->_product = $this->_coreRegistry->registry('product');
        }
        return $this->_product;
    }

    /**
     * $excludeAttr is optional array of attribute codes to exclude them from additional data array
     *
     * @param array $excludeAttr
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAdditionalData(array $excludeAttr = [])
    {
        $data = [];
        $product = $this->getProduct();
        $attributes = $product->getAttributes();
        foreach ($attributes as $attribute) {
            if ($this->isVisibleOnFrontend($attribute, $excludeAttr)) {
                $value = $attribute->getFrontend()->getValue($product);

                $value = $this->formatAttributeValue($attribute, $value);

                if (is_string($value) && strlen(trim($value))) {
                    $data[$attribute->getAttributeCode()] = [
                        'label' => $attribute->getStoreLabel(),
                        'value' => $value,
                        'code' => $attribute->getAttributeCode(),
                    ];
                }
            }
        }
        return $data;
    }

    /**
     * Format attribute value for frontend display
     *
     * @param AbstractAttribute $attribute
     * @param mixed $value
     * @return mixed
     */
    private function formatAttributeValue(
        AbstractAttribute $attribute,
        mixed $value
    ): mixed {
        if ($value instanceof Phrase) {
            return (string)$value;
        }
        if ($attribute->getFrontendInput() == 'price' && is_string($value)) {
            return $this->priceCurrency->convertAndFormat($value);
        }
        if ($attribute->getAttributeCode() === 'weight' && is_string($value)) {
            $weightUnit = $this->directoryHelper->getWeightUnit();
            if ($weightUnit) {
                return $value . ' ' . $weightUnit;
            }
        }
        return $value;
    }

    /**
     * Determine if we should display the attribute on the front-end
     *
     * @param AbstractAttribute $attribute
     * @param array $excludeAttr
     * @return bool
     * @since 103.0.0
     */
    protected function isVisibleOnFrontend(
        AbstractAttribute $attribute,
        array $excludeAttr
    ) {
        return ($attribute->getIsVisibleOnFront() && !in_array($attribute->getAttributeCode(), $excludeAttr));
    }
}
