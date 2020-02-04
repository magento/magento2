<?php
/**
 * Google Optimizer Category Block
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GoogleOptimizer\Block\Code;

/**
 * @api
 * @since 100.0.2
 */
class Category extends \Magento\GoogleOptimizer\Block\AbstractCode implements
    \Magento\Framework\DataObject\IdentityInterface
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
