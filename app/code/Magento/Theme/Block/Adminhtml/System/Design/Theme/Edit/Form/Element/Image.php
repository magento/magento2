<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Form\Element;

/**
 * Image form element that generates correct thumbnail image URL for theme preview image
 *
 * @method \Magento\Theme\Model\Theme getTheme()
 * @since 2.0.0
 */
class Image extends \Magento\Framework\Data\Form\Element\Image
{
    /**
     * Get image preview url
     *
     * @return string
     * @since 2.0.0
     */
    protected function _getUrl()
    {
        return $this->getTheme() ? $this->getTheme()->getThemeImage()->getPreviewImageUrl() : null;
    }
}
