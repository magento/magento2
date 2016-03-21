<?php
/**
 * Google Optimizer Product Tab
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Block\Adminhtml\Catalog\Product\Edit\Tab;

class Googleoptimizer extends \Magento\GoogleOptimizer\Block\Adminhtml\AbstractTab
{
    /**
     * Get Product entity
     *
     * @return \Magento\Catalog\Model\Product
     * @throws \RuntimeException
     */
    protected function _getEntity()
    {
        $entity = $this->_registry->registry('product');
        if (!$entity) {
            throw new \RuntimeException('Entity is not found in registry.');
        }
        return $entity;
    }

    /**
     * Return Tab label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Product View Optimization');
    }

    /**
     * Return Tab title
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Product View Optimization');
    }
}
