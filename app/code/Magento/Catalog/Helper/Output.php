<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Helper;

use Magento\Catalog\Model\Category as ModelCategory;
use Magento\Catalog\Model\Product as ModelProduct;
use Magento\Framework\Filter\Template;

class Output extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Array of existing handlers
     *
     * @var array
     */
    protected $_handlers;

    /**
     * Template processor instance
     *
     * @var Template
     */
    protected $_templateProcessor = null;

    /**
     * Catalog data
     *
     * @var Data
     */
    protected $_catalogData = null;

    /**
     * Eav config
     *
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param Data $catalogData
     * @param \Magento\Framework\Escaper $escaper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Eav\Model\Config $eavConfig,
        Data $catalogData,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->_eavConfig = $eavConfig;
        $this->_catalogData = $catalogData;
        $this->_escaper = $escaper;
        parent::__construct($context);
    }

    /**
     * @return Template
     */
    protected function _getTemplateProcessor()
    {
        if (null === $this->_templateProcessor) {
            $this->_templateProcessor = $this->_catalogData->getPageTemplateProcessor();
        }

        return $this->_templateProcessor;
    }

    /**
     * Adding method handler
     *
     * @param string $method
     * @param object $handler
     * @return $this
     */
    public function addHandler($method, $handler)
    {
        if (!is_object($handler)) {
            return $this;
        }
        $method = strtolower($method);

        if (!isset($this->_handlers[$method])) {
            $this->_handlers[$method] = [];
        }

        $this->_handlers[$method][] = $handler;
        return $this;
    }

    /**
     * Get all handlers for some method
     *
     * @param string $method
     * @return array
     */
    public function getHandlers($method)
    {
        $method = strtolower($method);
        return isset($this->_handlers[$method]) ? $this->_handlers[$method] : [];
    }

    /**
     * Process all method handlers
     *
     * @param string $method
     * @param mixed $result
     * @param array $params
     * @return mixed
     */
    public function process($method, $result, $params)
    {
        foreach ($this->getHandlers($method) as $handler) {
            if (method_exists($handler, $method)) {
                $result = $handler->{$method}($this, $result, $params);
            }
        }
        return $result;
    }

    /**
     * Prepare product attribute html output
     *
     * @param ModelProduct $product
     * @param string $attributeHtml
     * @param string $attributeName
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function productAttribute($product, $attributeHtml, $attributeName)
    {
        $attribute = $this->_eavConfig->getAttribute(ModelProduct::ENTITY, $attributeName);
        if ($attribute &&
            $attribute->getId() &&
            $attribute->getFrontendInput() != 'media_image' &&
            (!$attribute->getIsHtmlAllowedOnFront() &&
            !$attribute->getIsWysiwygEnabled())
        ) {
            if ($attribute->getFrontendInput() != 'price') {
                $attributeHtml = $this->_escaper->escapeHtml($attributeHtml);
            }
            if ($attribute->getFrontendInput() == 'textarea') {
                $attributeHtml = nl2br($attributeHtml);
            }
        }
        if ($attribute->getIsHtmlAllowedOnFront() && $attribute->getIsWysiwygEnabled()) {
            if ($this->_catalogData->isUrlDirectivesParsingAllowed()) {
                $attributeHtml = $this->_getTemplateProcessor()->filter($attributeHtml);
            }
        }

        $attributeHtml = $this->process(
            'productAttribute',
            $attributeHtml,
            ['product' => $product, 'attribute' => $attributeName]
        );

        return $attributeHtml;
    }

    /**
     * Prepare category attribute html output
     *
     * @param ModelCategory $category
     * @param string $attributeHtml
     * @param string $attributeName
     * @return string
     */
    public function categoryAttribute($category, $attributeHtml, $attributeName)
    {
        $attribute = $this->_eavConfig->getAttribute(ModelCategory::ENTITY, $attributeName);

        if ($attribute &&
            $attribute->getFrontendInput() != 'image' &&
            (!$attribute->getIsHtmlAllowedOnFront() &&
            !$attribute->getIsWysiwygEnabled())
        ) {
            $attributeHtml = $this->_escaper->escapeHtml($attributeHtml);
        }
        if ($attribute->getIsHtmlAllowedOnFront() && $attribute->getIsWysiwygEnabled()) {
            if ($this->_catalogData->isUrlDirectivesParsingAllowed()) {
                $attributeHtml = $this->_getTemplateProcessor()->filter($attributeHtml);
            }
        }
        $attributeHtml = $this->process(
            'categoryAttribute',
            $attributeHtml,
            ['category' => $category, 'attribute' => $attributeName]
        );
        return $attributeHtml;
    }
}
