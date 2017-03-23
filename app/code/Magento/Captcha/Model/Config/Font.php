<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Captcha image model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Captcha\Model\Config;

class Font implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Captcha data
     *
     * @var \Magento\Captcha\Helper\Data
     */
    protected $_captchaData = null;

    /**
     * @param \Magento\Captcha\Helper\Data $captchaData
     */
    public function __construct(\Magento\Captcha\Helper\Data $captchaData)
    {
        $this->_captchaData = $captchaData;
    }

    /**
     * Get options for font selection field
     *
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];
        foreach ($this->_captchaData->getFonts() as $fontName => $fontData) {
            $optionArray[] = ['label' => $fontData['label'], 'value' => $fontName];
        }
        return $optionArray;
    }
}
