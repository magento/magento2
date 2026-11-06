<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

/**
 * Captcha image model
 */
namespace Magento\Captcha\Model\Config;

class Font implements \Magento\Framework\Option\ArrayInterface
{
    /**
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
