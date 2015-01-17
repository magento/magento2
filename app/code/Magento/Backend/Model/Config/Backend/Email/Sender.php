<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System config email sender field backend model
 */
namespace Magento\Backend\Model\Config\Backend\Email;

class Sender extends \Magento\Framework\App\Config\Value
{
    /**
     * Check sender name validity
     *
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if (!preg_match("/^[\S ]+$/", $value)) {
            throw new \Magento\Framework\Model\Exception(
                __('The sender name "%1" is not valid. Please use only visible characters and spaces.', $value)
            );
        }

        if (strlen($value) > 255) {
            throw new \Magento\Framework\Model\Exception(__('Maximum sender name length is 255. Please correct your settings.'));
        }
        return $this;
    }
}
