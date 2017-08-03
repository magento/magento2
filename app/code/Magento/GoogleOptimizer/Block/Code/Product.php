<?php
/**
 * Google Optmizer Product Block
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\GoogleOptimizer\Block\Code;

/**
 * @api
 * @since 2.0.0
 */
class Product extends \Magento\GoogleOptimizer\Block\AbstractCode implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * @var Product name in registry
     * @since 2.0.0
     */
    protected $_registryName = 'current_product';

    /**
     * Return identifiers for produced content
     *
     * @return array
     * @since 2.0.0
     */
    public function getIdentities()
    {
        return $this->_getEntity()->getIdentities();
    }
}
