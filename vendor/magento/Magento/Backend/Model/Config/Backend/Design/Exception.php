<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Backend\Model\Config\Backend\Design;

class Exception extends \Magento\Backend\Model\Config\Backend\Serialized\ArraySerialized
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'core_config_backend_design_exception';
}
