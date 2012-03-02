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
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Catalog_Helper_Output extends Mage_Core_Helper_Abstract
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
     * @var Varien_Filter_Template
     */
    protected $_templateProcessor = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        Mage::dispatchEvent('catalog_helper_output_construct', array('helper'=>$this));
    }

    protected function _getTemplateProcessor()
    {
        if (null === $this->_templateProcessor) {
            $this->_templateProcessor = Mage::helper('Mage_Catalog_Helper_Data')->getPageTemplateProcessor();
        }

        return $this->_templateProcessor;
    }

    /**
     * Adding method handler
     *
     * @param   string $method
     * @param   object $handler
     * @return  Mage_Catalog_Helper_Output
     */
    public function addHandler($method, $handler)
    {
        if (!is_object($handler)) {
            return $this;
        }
        $method = strtolower($method);

        if (!isset($this->_handlers[$method])) {
            $this->_handlers[$method] = array();
        }

        $this->_handlers[$method][] = $handler;
        return $this;
    }

    /**
     * Get all handlers for some method
     *
     * @param   string $method
     * @return  array
     */
    public function getHandlers($method)
    {
        $method = strtolower($method);
        return isset($this->_handlers[$method]) ? $this->_handlers[$method] : array();
    }

    /**
     * Process all method handlers
     *
     * @param   string $method
     * @param   mixed $result
     * @param   array $params
     * @return unknown
     */
    public function process($method, $result, $params)
    {
        foreach ($this->getHandlers($method) as $handler) {
            if (method_exists($handler, $method)) {
                $result = $handler->$method($this, $result, $params);
            }
        }
        return $result;
    }

    /**
     * Prepare product attribute html output
     *
     * @param   Mage_Catalog_Model_Product $product
     * @param   string $attributeHtml
     * @param   string $attributeName
     * @return  string
     */
    public function productAttribute($product, $attributeHtml, $attributeName)
    {
        $attribute = Mage::getSingleton('Mage_Eav_Model_Config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attributeName);
        if ($attribute && $attribute->getId() && ($attribute->getFrontendInput() != 'media_image')
            && (!$attribute->getIsHtmlAllowedOnFront() && !$attribute->getIsWysiwygEnabled())) {
                if ($attribute->getFrontendInput() != 'price') {
                    $attributeHtml = $this->escapeHtml($attributeHtml);
                }
                if ($attribute->getFrontendInput() == 'textarea') {
                    $attributeHtml = nl2br($attributeHtml);
                }
        }
        if ($attribute->getIsHtmlAllowedOnFront() && $attribute->getIsWysiwygEnabled()) {
            if (Mage::helper('Mage_Catalog_Helper_Data')->isUrlDirectivesParsingAllowed()) {
                $attributeHtml = $this->_getTemplateProcessor()->filter($attributeHtml);
            }
        }

        $attributeHtml = $this->process('productAttribute', $attributeHtml, array(
            'product'   => $product,
            'attribute' => $attributeName
        ));

        return $attributeHtml;
    }

    /**
     * Prepare category attribute html output
     *
     * @param   Mage_Catalog_Model_Category $category
     * @param   string $attributeHtml
     * @param   string $attributeName
     * @return  string
     */
    public function categoryAttribute($category, $attributeHtml, $attributeName)
    {
        $attribute = Mage::getSingleton('Mage_Eav_Model_Config')->getAttribute(Mage_Catalog_Model_Category::ENTITY, $attributeName);

        if ($attribute && ($attribute->getFrontendInput() != 'image')
            && (!$attribute->getIsHtmlAllowedOnFront() && !$attribute->getIsWysiwygEnabled())) {
            $attributeHtml = $this->escapeHtml($attributeHtml);
        }
        if ($attribute->getIsHtmlAllowedOnFront() && $attribute->getIsWysiwygEnabled()) {
            if (Mage::helper('Mage_Catalog_Helper_Data')->isUrlDirectivesParsingAllowed()) {
                $attributeHtml = $this->_getTemplateProcessor()->filter($attributeHtml);
            }
        }
        $attributeHtml = $this->process('categoryAttribute', $attributeHtml, array(
            'category'  => $category,
            'attribute' => $attributeName
        ));
        return $attributeHtml;
    }
}
