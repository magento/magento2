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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product form image field helper
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Catalog_Product_Helper_Form_BaseImage extends Varien_Data_Form_Element_Hidden
{
    /**
     * Maximum file size to upload in bytes.
     *
     * @var int
     */
    protected $_maxFileSize;

    /**
     * Media Uploader instance
     *
     * @var Mage_Adminhtml_Block_Media_Uploader
     */
    protected $_mediaUploader;

    /**
     * Model Url instance
     *
     * @var Mage_Backend_Model_Url
     */
    protected $_url;

    /**
     * Media Config instance
     *
     * @var Mage_Catalog_Model_Product_Media_Config
     */
    protected $_mediaConfig;

    /**
     * Design Package instance
     *
     * @var Mage_Core_Model_Design_Package
     */
    protected $_design;

    /**
     * Data instance
     *
     * @var Mage_Core_Helper_Data
     */
    protected $_helperData;

    /**
     * Constructor
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);

        $this->_mediaUploader = isset($attributes['mediaUploader']) ? $attributes['mediaUploader']
            : Mage::getSingleton('Mage_Adminhtml_Block_Media_Uploader');
        $this->_url = isset($attributes['url']) ? $attributes['url']
            : Mage::getModel('Mage_Backend_Model_Url');
        $this->_mediaConfig = isset($attributes['mediaConfig']) ? $attributes['mediaConfig']
            : Mage::getSingleton('Mage_Catalog_Model_Product_Media_Config');
        $this->_design = isset($attributes['design']) ? $attributes['design']
            : Mage::getSingleton('Mage_Core_Model_Design_Package');
        $this->_helperData = isset($attributes['helperData']) ? $attributes['helperData']
            : Mage::helper('Mage_Core_Helper_Data');

        $this->_maxFileSize = $this->_getFileMaxSize();
    }

    /**
     * Return element html code
     *
     * @return string
     */
    public function getElementHtml()
    {
        $imageUrl = $this->_helperData->escapeHtml($this->_getImageUrl($this->getValue()));
        $htmlId = $this->_helperData->escapeHtml($this->getHtmlId());
        $uploadUrl = $this->_helperData->escapeHtml($this->_getUploadUrl());

        $html = '<input id="' . $htmlId .'_upload" type="file" name="image" '
                 . 'data-url="' . $uploadUrl . '" style="display: none;" />'
                 . parent::getElementHtml()
                 . '<img align="left" src="' . $imageUrl . '" id="' . $htmlId . '_image"'
                 . ' title="' . $imageUrl . '" alt="' . $imageUrl . '" class="base-image-uploader"'
                 . ' onclick="jQuery(\'#' . $htmlId . '_upload\').trigger(\'click\')"/>';
        $html .= $this->_getJs();

        return $html;
    }

    /**
     * Get js for image uploader
     *
     * @return string
     */
    protected function _getJs()
    {
        return "<script>/* <![CDATA[ */"
               . "jQuery(function(){"
               . "BaseImageUploader({$this->_helperData->jsonEncode($this->getHtmlId())}, "
               . "{$this->_helperData->jsonEncode($this->_maxFileSize)});"
               . " });"
               . "/*]]>*/</script>";
    }

    /**
     * Get full url for image
     *
     * @param string $imagePath
     *
     * @return string
     */
    protected function _getImageUrl($imagePath)
    {
        if (!in_array($imagePath, array(null, 'no_selection', '/'))) {
            if (pathinfo($imagePath, PATHINFO_EXTENSION) == 'tmp') {
                $imageUrl = $this->_mediaConfig->getTmpMediaUrl(substr($imagePath, 0, -4));
            } else {
                $imageUrl = $this->_mediaConfig->getMediaUrl($imagePath);
            }
        } else {
            $imageUrl = $this->_design->getViewFileUrl('Mage_Adminhtml::images/image-placeholder.png');
        }

        return $imageUrl;
    }

    /**
     * Get url to upload files
     *
     * @return string
     */
    protected function _getUploadUrl()
    {
        return $this->_url->getUrl('*/catalog_product_gallery/upload');
    }

    /**
     * Get maximum file size to upload in bytes
     *
     * @return int
     */
    protected function _getFileMaxSize()
    {
        return $this->_mediaUploader->getDataMaxSizeInBytes();
    }
}
