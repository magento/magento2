<?php
/**
 * Google Optimizer Category Block
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
class Category extends \Magento\GoogleOptimizer\Block\AbstractCode implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * @var string Entity name in registry
     * @since 2.0.0
     */
    protected $_registryName = 'current_category';

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
