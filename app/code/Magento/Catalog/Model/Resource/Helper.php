<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Model\Resource;

/**
 * Eav Mysql resource helper model
 */
class Helper extends \Magento\Eav\Model\Resource\Helper
{
    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param string $modulePrefix
     */
    public function __construct(\Magento\Framework\App\Resource $resource, $modulePrefix = 'Magento_Catalog')
    {
        parent::__construct($resource, $modulePrefix);
    }
}
