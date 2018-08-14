<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cookie\Model\Config\Backend;

/**
 * Config Cookie Restriction mode backend
 */
class Cookie extends \Magento\Framework\App\Config\Value
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'adminhtml_system_config_backend_cookie';
}
