<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
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
use Magento\Framework\Phrase;
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
     * Catalog data helper
     *
     * @var Data
     */
    protected $_catalogData = null;

    /**
     * Eav config model
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
     * @param string|Phrase $attributeHtml
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
            && $this->isDirectivesExists((string)$attributeHtml)
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
            && $this->isDirectivesExists((string)$attributeHtml)

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
     * @param string|Phrase $attributeHtml
     * @return bool
     */
    public function isDirectivesExists(string $attributeHtml): bool
    {
        $matches = false;
        foreach ($this->directivePatterns as $pattern) {
            if (preg_match($pattern, (string)$attributeHtml)) {
                $matches = true;
                break;
            }
        }
        return $matches;
    }

    /**
     * Process attribute string
     *
     * @param string $attributeString
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function processString(string $attributeString): string
    {
        if (empty($attributeString)) {
            return '';
        }

        $trimmed = trim($attributeString);

        if (!preg_match_all('/([a-z0-9_-]+)(?:=(["\'])([^"\']*)\2)?/i', $trimmed, $matches, PREG_SET_ORDER)) {
            $attrName = strtolower($trimmed);
            if ($this->validatePattern($attrName)) {
                return $trimmed;
            }
            return $this->_escaper->escapeHtmlAttr($attributeString);
        }

        foreach ($matches as $match) {
            $attrName = strtolower(trim($match[1]));

            if (!$this->validatePattern($attrName)) {
                return $this->_escaper->escapeHtmlAttr($attributeString);
            }

            if (isset($match[3]) && $this->validateContent($match[3])) {
                return $this->_escaper->escapeHtmlAttr($attributeString);
            }
        }

        $parsed = preg_replace_callback(
            '/(=["\'])([^"\']*)(["\'])/',
            function ($m) {
                return $m[1] . $this->_escaper->escapeHtmlAttr($m[2]) . $m[3];
            },
            $attributeString
        );

        return $parsed;
    }

    /**
     * Validate string pattern
     *
     * @param string $attrName
     * @return bool
     */
    private function validatePattern(string $attrName): bool
    {
        if (strpos($attrName, 'data-') === 0) {
            $suffix = substr($attrName, 5);
            return preg_match('/^[a-z0-9_-]+$/', $suffix) === 1;
        }

        if (strpos($attrName, 'on') === 0 && strlen($attrName) > 2) {
            return false;
        }

        return preg_match('/(script|eval|expression|import|binding)/i', $attrName) !== 1;
    }

    /**
     * Validate string content
     *
     * @param string $attrValue
     * @return bool
     */
    private function validateContent(string $attrValue): bool
    {
        return preg_match('/(javascript|data|vbscript)\s*:/i', $attrValue) === 1;
    }
}
