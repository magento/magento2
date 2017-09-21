<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * System config email sender field backend model
 */
namespace Magento\Config\Model\Config\Backend\Email;

/**
 * @api
 * @since 100.0.2
 */
class Sender extends \Magento\Framework\App\Config\Value
{
    /**
     * Check sender name validity
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if (!preg_match("/^[\S ]+$/", $value)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The sender name "%1" is not valid. Please use only visible characters and spaces.', $value)
            );
        }

        if (strlen($value) > 255) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Maximum sender name length is 255. Please correct your settings.'));
        }
        return $this;
    }
}
