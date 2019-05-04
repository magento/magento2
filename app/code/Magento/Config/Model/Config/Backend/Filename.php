<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Backend;

/**
 * @api
 * @since 100.0.2
 */
class Filename extends \Magento\Framework\App\Config\Value
{
    /**
     * @return $this
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        $value = basename($value);
        $this->setValue($value);
        return $this;
    }
}
