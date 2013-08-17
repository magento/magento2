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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product form image field helper
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Catalog_Product_Helper_Form_BaseImage extends Varien_Data_Form_Element_Abstract
{
    /**
     * Model Url instance
     *
     * @var Mage_Backend_Model_Url
     */
    protected $_url;

    /**
     * @var Mage_Core_Helper_Data
     */
    protected $_coreHelper;

    /**
     * @var Mage_Catalog_Helper_Data
     */
    protected $_catalogHelperData;

    /**
     * @var Magento_File_Size
     */
    protected $_fileConfig;

    /**
     * @var Mage_Core_Model_View_Url
     */
    protected $_viewUrl;

    /**
     * Constructor
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);

        $this->_viewUrl = Mage::getModel('Mage_Core_Model_View_Url');

        $this->_url = isset($attributes['url']) ? $attributes['url']
            : Mage::getModel('Mage_Backend_Model_Url');
        $this->_coreHelper = isset($attributes['coreHelper']) ? $attributes['coreHelper']
            : Mage::helper('Mage_Core_Helper_Data');
        $this->_catalogHelperData = isset($attributes['catalogHelperData']) ? $attributes['catalogHelperData']
            : Mage::helper('Mage_Catalog_Helper_Data');
        $this->_fileConfig = isset($attributes['fileConfig']) ? $attributes['fileConfig'] :
            Mage::getSingleton('Magento_File_Size');
        $this->_maxFileSize = $this->_getFileMaxSize();
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->helper('Mage_Catalog_Helper_Data')->__('Images');
    }

    /**
     * Translate message
     *
     * @param string $message
     */
    private function __($message) {
        return $this->helper('Mage_Catalog_Helper_Data')->__($message);
    }

    /**
     * Return element html code
     *
     * @return string
     */
    public function getElementHtml()
    {
        $htmlId = $this->_coreHelper->escapeHtml($this->getHtmlId());
        $uploadUrl = $this->_coreHelper->escapeHtml($this->_getUploadUrl());
        $spacerImage = $this->_viewUrl->getViewFileUrl('images/spacer.gif');
        /** @var $product Mage_Catalog_Model_Product */
        $html = <<<HTML
<div id="{$htmlId}-container" class="images"
    data-mage-init="{baseImage:{}}"
    data-max-file-size="{$this->_getFileMaxSize()}"
    >
    <div class="image image-placeholder">
        <input type="file" name="image" data-url="{$uploadUrl}" multiple="multiple" />
        <img class="spacer" src="{$spacerImage}"/>
        <p class="image-placeholder-text">{$this->__('Click here or drag and drop to add images')}</p>
    </div>
    <script id="{$htmlId}-template" class="image-template" type="text/x-jquery-tmpl">
        <div class="image">
            <img class="spacer" src="{$spacerImage}"/>
            <img class="product-image" src="\${url}" data-position="\${position}" alt="\${label}" />
            <div class="actions">
                <button class="action-delete" data-role="delete-button" title="{$this->__('Delete image')}">
                    <span>{$this->__('Delete image')}</span>
                </button>
                <button class="action-make-base" data-role="make-base-button" title="{$this->__('Make Base')}">
                    <span>{$this->__('Make Base')}</span>
                </button>
                <div class="draggable-handle"></div>
            </div>
            <div class="image-label"></div>
            <div class="image-fade"><span>{$this->__('Hidden')}</span></div>
        </div>
    </script>
</div>
<span class="action-manage-images" data-activate-tab="image-management">
    <span>{$this->helper('Mage_Catalog_Helper_Data')->__('Image Management')}</span>
</span>
<script>
    (function($) {
        'use strict';

        $('[data-activate-tab=image-management]')
            .on('click.toggleImageManagementTab', function() {
                $('#product_info_tabs_image-management').trigger('click');
            });
    })(window.jQuery);
</script>

HTML;
        return $html;
    }

    /**
     * Get url to upload files
     *
     * @return string
     */
    protected function _getUploadUrl()
    {
        return $this->_url->getUrl('adminhtml/catalog_product_gallery/upload');
    }

    /**
     * Get maximum file size to upload in bytes
     *
     * @return int
     */
    protected function _getFileMaxSize()
    {
        return $this->_fileConfig->getMaxFileSize();
    }

    /**
     * Dummy function to give translation tool the ability to pick messages
     * Must be called with Mage_Catalog_Helper_Data $className only
     *
     * @param string $className
     * @return Mage_Catalog_Helper_Data|Mage_Core_Helper_Data
     */
    private function helper($className)
    {
        return $className === 'Mage_Catalog_Helper_Data' ? $this->_catalogHelperData : $this->_coreHelper;
    }
}
