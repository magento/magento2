<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Config\Backend;

use Magento\Framework\Exception\LocalizedException;

/**
 * A backend model for verticals configuration.
 */
class Vertical extends \Magento\Framework\App\Config\Value
{
    /**
     * Handles the value of the selected vertical before saving.
     *
     * @return $this
     * @throws LocalizedException if the value of the selected vertical is empty.
     */
    public function beforeSave()
    {
        if (empty($this->getValue())) {
            throw new LocalizedException(__('Please select a vertical.'));
        }

        return $this;
    }
}
