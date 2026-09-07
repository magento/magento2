<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

/**
 * Config category field backend
 */
namespace Magento\Config\Model\Config\Backend;

/**
 * @api
 * @since 100.0.2
 */
class Datashare extends \Magento\Framework\App\Config\Value
{
    /**
     * Do nothing after save
     *
     * @return $this
     */
    public function afterSave()
    {
        return $this;
    }
}
