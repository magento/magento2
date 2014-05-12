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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $optionArray = array();
        foreach ($this->_captchaData->getFonts() as $fontName => $fontData) {
            $optionArray[] = array('label' => $fontData['label'], 'value' => $fontName);
        }
        return $optionArray;
    }
}
