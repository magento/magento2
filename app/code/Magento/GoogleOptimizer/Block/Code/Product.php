<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\GoogleOptimizer\Block\Code;

/**
 * @api
 * @since 100.0.2
 */
class Product extends \Magento\GoogleOptimizer\Block\AbstractCode implements
    \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * @var Product name in registry
     */
    protected $_registryName = 'current_product';

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return $this->_getEntity()->getIdentities();
    }
}
