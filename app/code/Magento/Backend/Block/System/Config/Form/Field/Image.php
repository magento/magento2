<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Image config field renderer
 */
namespace Magento\Backend\Block\System\Config\Form\Field;

/**
 * Class Image Field
 * @method getFieldConfig()
 * @method setFieldConfig()
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
        $url = parent::_getUrl();
        $config = $this->getFieldConfig();
        /* @var $config array */
        if (isset($config['base_url'])) {
            $element = $config['base_url'];
            $urlType = empty($element['type']) ? 'link' : (string)$element['type'];
            $url = $this->_urlBuilder->getBaseUrl(['_type' => $urlType]) . $element['value'] . '/' . $url;
        }
        return $url;
    }
}
