<?php
/**
 * Google Optmizer Product Block
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GoogleOptimizer\Block\Code;

use Magento\Framework\DataObject\IdentityInterface;

class Product extends \Magento\GoogleOptimizer\Block\AbstractCode implements IdentityInterface
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
