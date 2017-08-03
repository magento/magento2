<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel;

/**
 * Eav Mysql resource helper model
 * @since 2.0.0
 */
class Helper extends \Magento\Eav\Model\ResourceModel\Helper
{
    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param string $modulePrefix
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\App\ResourceConnection $resource, $modulePrefix = 'Magento_Catalog')
    {
        parent::__construct($resource, $modulePrefix);
    }
}
