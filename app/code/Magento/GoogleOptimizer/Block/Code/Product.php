<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GoogleOptimizer\Block\Code;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\GoogleOptimizer\Block\AbstractCode;

/**
 * Google Optmizer Product Block.
 *
 * @api
 * @since 100.0.2
 */
class Product extends AbstractCode implements IdentityInterface
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
