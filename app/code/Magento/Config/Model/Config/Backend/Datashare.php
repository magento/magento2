<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Config category field backend
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Config\Model\Config\Backend;

class Datashare extends \Magento\Framework\App\Config\Value
{
    /**
     * @return $this
     */
    public function afterSave()
    {
        return $this;
    }
}
