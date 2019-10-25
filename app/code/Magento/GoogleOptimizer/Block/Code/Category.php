<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GoogleOptimizer\Block\Code;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\GoogleOptimizer\Block\AbstractCode;

/**
 * Google Optimizer Category Block.
 *
 * @api
 * @since 100.0.2
 */
class Category extends AbstractCode implements IdentityInterface
{
    /**
     * @var string Entity name in registry
     */
    protected $_registryName = 'current_category';

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
