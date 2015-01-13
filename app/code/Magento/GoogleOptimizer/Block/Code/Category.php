<?php
/**
 * Google Optimizer Category Block
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Block\Code;

class Category extends \Magento\GoogleOptimizer\Block\AbstractCode implements \Magento\Framework\View\Block\IdentityInterface
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
