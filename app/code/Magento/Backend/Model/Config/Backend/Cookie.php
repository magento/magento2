<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Config Cookie Restriction mode backend
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Backend\Model\Config\Backend;

class Cookie extends \Magento\Framework\App\Config\Value
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'adminhtml_system_config_backend_cookie';
}
