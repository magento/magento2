<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Helper;

use Magento\Catalog\Model\Category as ModelCategory;
use Magento\Catalog\Model\Product as ModelProduct;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filter\Template;
use function is_object;
use function method_exists;
use function preg_match;
use function strtolower;

/**
 * Html output
 */
class Output extends AbstractHelper
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
     * @var Config
     */
    protected $_eavConfig;

    /**
     * @var Escaper
     */
    protected $_escaper;

    /**
     * @var array
     */
    private $directivePatterns;

    /**
     * Output constructor.
     * @param Context $context
     * @param Config $eavConfig
     * @param Data $catalogData
     * @param Escaper $escaper
     * @param array $directivePatterns
     * @param array $handlers
     */
    public function __construct(
        Context $context,
        Config $eavConfig,
        Data $catalogData,
        Escaper $escaper,
        $directivePatterns = [],
        array $handlers = []
    ) {
        $this->_eavConfig = $eavConfig;
        $this->_catalogData = $catalogData;
        $this->_escaper = $escaper;
        $this->directivePatterns = $directivePatterns;
        $this->_handlers = $handlers;
        parent::__construct($context);
    }

    /**
     * Return template processor
     *
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
        return $this->_handlers[strtolower($method)] ?? [];
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
     * @throws LocalizedException
     */
    public function productAttribute($product, $attributeHtml, $attributeName)
    {
        $attribute = $this->_eavConfig->getAttribute(ModelProduct::ENTITY, $attributeName);
        if ($attribute &&
            $attribute->getId() &&
            $attribute->getFrontendInput() !== 'media_image' &&
            (!$attribute->getIsHtmlAllowedOnFront() &&
            !$attribute->getIsWysiwygEnabled())
        ) {
            if ($attribute->getFrontendInput() !== 'price') {
                $attributeHtml = $this->_escaper->escapeHtml($attributeHtml);
            }
            if ($attribute->getFrontendInput() === 'textarea') {
                $attributeHtml = nl2br($attributeHtml);
            }
        }
        if ($attributeHtml !== null
            && $attribute->getIsHtmlAllowedOnFront()
            && $attribute->getIsWysiwygEnabled()
            && $this->isDirectivesExists($attributeHtml)
        ) {
            $attributeHtml = $this->_getTemplateProcessor()->filter($attributeHtml);
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
     * @throws LocalizedException
     */
    public function categoryAttribute($category, $attributeHtml, $attributeName)
    {
        $attribute = $this->_eavConfig->getAttribute(ModelCategory::ENTITY, $attributeName);

        if ($attribute &&
            $attribute->getFrontendInput() !== 'image' &&
            (!$attribute->getIsHtmlAllowedOnFront() &&
            !$attribute->getIsWysiwygEnabled())
        ) {
            $attributeHtml = $this->_escaper->escapeHtml($attributeHtml);
        }
        if ($attributeHtml !== null
            && $attribute->getIsHtmlAllowedOnFront()
            && $attribute->getIsWysiwygEnabled()
            && $this->isDirectivesExists($attributeHtml)

        ) {
            $attributeHtml = $this->_getTemplateProcessor()->filter($attributeHtml);
        }
        $attributeHtml = $this->process(
            'categoryAttribute',
            $attributeHtml,
            ['category' => $category, 'attribute' => $attributeName]
        );
        return $attributeHtml;
    }

    /**
     * Check if string has directives
     *
     * @param string $attributeHtml
     * @return bool
     */
    public function isDirectivesExists($attributeHtml)
    {
        $matches = false;
        foreach ($this->directivePatterns as $pattern) {
            if (preg_match($pattern, $attributeHtml)) {
                $matches = true;
                break;
            }
        }
        return $matches;
    }
}
