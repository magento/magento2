<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

/**
 * System config email field backend model
 */
declare(strict_types=1);

namespace Magento\Security\Model\Config\Backend\Session;

use Magento\Framework\App\Config\Value;

/**
 * Backend Model for Max Session Size
 */
class SessionSize extends Value
{
    /**
     * Handles the before save event
     *
     * @return $this
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if ($value === '0') {
            $value = 0;
        } else {
            $value = (int)$value;
            if ($value === null || $value <= 0) {
                $value = 256000;
            }
        }
        $this->setValue((string)$value);
        return $this;
    }
}
