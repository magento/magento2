<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Backend;

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
