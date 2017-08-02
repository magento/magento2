<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product form image field helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Helper\Form;

/**
 * Class \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Image
 *
 * @since 2.0.0
 */
class Image extends \Magento\Framework\Data\Form\Element\Image
{
    /**
     * @return bool|string
     * @since 2.0.0
     */
    protected function _getUrl()
    {
        $url = false;
        if ($this->getValue()) {
            $url = $this->_urlBuilder->getBaseUrl(
                ['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]
            ) . 'catalog/product/' . $this->getValue();
        }
        return $url;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    protected function _getDeleteCheckbox()
    {
        $html = '';
        if ($attribute = $this->getEntityAttribute()) {
            if (!$attribute->getIsRequired()) {
                $html .= parent::_getDeleteCheckbox();
            } else {
                $inputField = '<input value="%s" id="%s_hidden" type="hidden" class="required-entry" />';
                $html .= sprintf($inputField, $this->getValue(), $this->getHtmlId());
                $html .= '<script>require(["prototype"], function(){
                    syncOnchangeValue(\'' .
                    $this->getHtmlId() .
                    '\', \'' .
                    $this->getHtmlId() .
                    '_hidden\');
                });
                </script>';
            }
        } else {
            $html .= parent::_getDeleteCheckbox();
        }
        return $html;
    }
}
