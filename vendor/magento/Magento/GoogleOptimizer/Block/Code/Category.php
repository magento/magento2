<?php
/**
 * Google Optimizer Category Block
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
