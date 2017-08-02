<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cookie\Model\Config\Backend;

/**
 * Config Cookie Restriction mode backend
 * @since 2.0.0
 */
class Cookie extends \Magento\Framework\App\Config\Value
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_eventPrefix = 'adminhtml_system_config_backend_cookie';
}
