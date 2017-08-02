<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customer Widget Form Image File Element Block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Customer\Block\Adminhtml\Form\Element;

/**
 * Class \Magento\Customer\Block\Adminhtml\Form\Element\Image
 *
 * @since 2.0.0
 */
class Image extends \Magento\Customer\Block\Adminhtml\Form\Element\File
{
    /**
     * Return Delete CheckBox Label
     *
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    protected function _getDeleteCheckboxLabel()
    {
        return __('Delete Image');
    }

    /**
     * Return Delete CheckBox SPAN Class name
     *
     * @return string
     * @since 2.0.0
     */
    protected function _getDeleteCheckboxSpanClass()
    {
        return 'delete-image';
    }

    /**
     * Return File preview link HTML
     *
     * @return string
     * @since 2.0.0
     */
    protected function _getPreviewHtml()
    {
        $html = '';
        if ($this->getValue() && !is_array($this->getValue())) {
            $url = $this->_getPreviewUrl();
            $imageId = sprintf('%s_image', $this->getHtmlId());
            $image = [
                'alt' => __('View Full Size'),
                'title' => __('View Full Size'),
                'src' => $url,
                'class' => 'small-image-preview v-middle',
                'height' => 22,
                'width' => 22,
                'id' => $imageId
            ];
            $link = ['href' => $url, 'onclick' => "imagePreview('{$imageId}'); return false;"];

            $html = sprintf(
                '%s%s</a> ',
                $this->_drawElementHtml('a', $link, false),
                $this->_drawElementHtml('img', $image)
            );
        }
        return $html;
    }

    /**
     * Return Image URL
     *
     * @return string
     * @since 2.0.0
     */
    protected function _getPreviewUrl()
    {
        return $this->_adminhtmlData->getUrl(
            'customer/index/viewfile',
            ['image' => $this->urlEncoder->encode($this->getValue())]
        );
    }
}
