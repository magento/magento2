<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Captcha image model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Captcha\Model\Config;

/**
 * Class \Magento\Captcha\Model\Config\Mode
 *
 * @since 2.0.0
 */
class Mode implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Get options for captcha mode selection field
     *
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return [
            ['label' => __('Always'), 'value' => \Magento\Captcha\Helper\Data::MODE_ALWAYS],
            [
                'label' => __('After number of attempts to login'),
                'value' => \Magento\Captcha\Helper\Data::MODE_AFTER_FAIL
            ]
        ];
    }
}
