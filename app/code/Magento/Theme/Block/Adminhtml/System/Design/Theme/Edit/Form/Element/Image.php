<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Form\Element;

/**
 * Image form element that generates correct thumbnail image URL for theme preview image
 *
 * @method \Magento\Core\Model\Theme getTheme()
 */
class Image extends \Magento\Framework\Data\Form\Element\Image
{
    /**
     * Get image preview url
     *
     * @return string
     */
    protected function _getUrl()
    {
        return $this->getTheme() ? $this->getTheme()->getThemeImage()->getPreviewImageUrl() : null;
    }
}
