<?php
/**
 * Google Optimizer Cms Page Tab
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
