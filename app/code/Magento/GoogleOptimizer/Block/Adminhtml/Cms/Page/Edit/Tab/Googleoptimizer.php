<?php
/**
 * Google Optimizer Cms Page Tab
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Block\Adminhtml\Cms\Page\Edit\Tab;

class Googleoptimizer extends \Magento\GoogleOptimizer\Block\Adminhtml\AbstractTab
{
    /**
     * Get cms page model
     *
     * @return mixed
     * @throws \RuntimeException
     */
    protected function _getEntity()
    {
        $entity = $this->_registry->registry('cms_page');
        if (!$entity) {
            throw new \RuntimeException('Entity is not found in registry.');
        }
        return $entity;
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Page View Optimization');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Page View Optimization');
    }
}
