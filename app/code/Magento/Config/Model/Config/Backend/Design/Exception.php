<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Backend\Design;

/**
 * @api
 */
class Exception extends \Magento\Config\Model\Config\Backend\Serialized\ArraySerialized
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'core_config_backend_design_exception';
}
